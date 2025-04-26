<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\Customer;
use App\Models\detailKonfigurasi;
use App\Models\Konfigurasi;
use App\Models\PosSession;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Sales_detail;
use App\Models\Suppliers;
use App\Models\User;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PosController extends Controller
{
    //

    public function index(Request $request)
    {
        $supplier = Suppliers::get();
        $customers = Customer::get();
        $categories = Categories::all();

        // Ambil parameter dari request
        $supplierId = $request->get('supplier_id');
        $categoryId = $request->get('category_id');


        // Query produk dengan filter
        $products = Product::with('productImage')
            ->when($supplierId, function ($query, $supplierId) {
                return $query->where('suppliers_id', $supplierId);
            })
            ->when($categoryId, function ($query, $categoryId) {
                return $query->where('categories_id', $categoryId);
            })
            ->get();

        $konfigurasi = Konfigurasi::with('details')->get();

        return view('pos.index', compact('supplier', 'products', 'customers', 'categories', 'konfigurasi'));
    }


    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:product,id',
            'cart.*.name' => 'required|string|max:255',
            'cart.*.price' => 'required|numeric|min:0',
            'cart.*.quantity' => 'required|integer|min:1',
            'discount' => 'required|numeric|min:0',
            'total_price' => 'required|numeric|min:0',
            'final_total' => 'required|numeric|min:0',
            'customer_id' => 'required|exists:customers,id',
            'payment_method_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        DB::beginTransaction();
        try {
            //code...
            $user = FacadesAuth::user();

            $user = User::where('id', $user->id)->with('employees')->first();


            $possession = PosSession::firstOrCreate(
                [
                    'users_id' => $user->id,
                    'Date' => Carbon::now()->toDateString()
                ],
                [
                    'cash_in' => 0,
                    'cash_out' => 0,
                    'session_status' => 'open',
                    'total_income' => 0
                ]
            );


            $sales = Sales::create(
                [
                    'employes_id' => $user->employees->first()->id,
                    'payment_methods_id' => $request->payment_method_id,
                    'customers_id' => $request->customer_id,
                    'total_price' => $request->final_total,
                    'shipping_cost' => 0,
                    'discount' => $request->discount,
                ]
            );

            foreach ($request->cart as $key => $value) {
                # code...
                Sales_detail::create(
                    [
                        'product_id' => $value['id'],
                        'sales_id' => $sales->id,
                        'total_quantity' => $value['quantity'],
                        'total_price' => $value['quantity'] * $value['price']
                    ]
                );


                $product = Product::find($value['id']);
                if ($product->stock >= $value['quantity']) {
                    $product->decrement('stock', $value['quantity']);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Stock product tidak mencukupi untuk melakukan pembelian!',
                        'data' => null
                    ], 400);
                }
            }

            $possession->update([
                'total_income' => $possession->total + $request->final_total,
                'cash_in' => $possession->cash_in + $request->final_total
            ]);

            DB::commit();

            return response()->json([
                'status' => true,
                'message' => 'success order !',
                'data' => $sales
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => $th->getMessage(),
                'data' => null
            ], 500);
        }
    }

    public function print($id)
    {
        $sales = Sales::where('id', $id)->with('salesDetail', 'paymentMethod', 'customer', 'salesDetail.product')->first();
        
        // Fetch the company information from the companies table
        $company = DB::table('companies')->first();
        
        // Increase the paper size to properly fit all content
        // Width: 250 points (about 88mm for thermal receipt)
        // Height: 800 points (increased from 600 to accommodate all content)
        $pdf = Pdf::loadView('pos.print', compact('sales', 'company'))
            ->setPaper([0, 0, 250, 800], 'portrait');
    
        return $pdf->stream('bukti-pembayaran.pdf');
    }
}
