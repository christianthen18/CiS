<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Retur;
use App\Models\Sales;
use App\Models\Sales_detail;
use Illuminate\Http\Request;

class SalesReturController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        
        // Start the query
        $query = Retur::with(['customer', 'product'])
            ->where('type', 'penjualan');
        
        // Apply search filter (for invoice number or customer name)
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', '%' . $search . '%')
                  ->orWhereHas('customer', function($query) use ($search) {
                      $query->where('name', 'like', '%' . $search . '%');
                  });
            });
        }
        
        // Apply status filter
        if ($status) {
            $query->where('status', $status);
        }
        
        // Apply date range filters
        if ($date_from) {
            $query->whereDate('created_at', '>=', $date_from);
        }
        
        if ($date_to) {
            $query->whereDate('created_at', '<=', $date_to);
        }
        
        // Execute query
        $returs = $query->get();
        
        // Get unique statuses for the filter dropdown
        $statuses = Retur::where('type', 'penjualan')
            ->distinct()
            ->pluck('status');
        
        return view('salesRetur.index', compact('returs', 'statuses', 'search', 'status', 'date_from', 'date_to'));
    }
    public function detail($id)
    {
        // Fetch the sale with its details
        $retur = Retur::with(['customer', 'product']) // Eager load related data
            ->findOrFail($id); // Fetch the sale or fail if not found

        return view('salesRetur.detail', compact('retur'));
    }

    public function updateStatus(Request $request, $id)
    {
        // Find the return record
        $retur = Retur::findOrFail($id);

        // Update the status
        $retur->status = $request->status;
        $retur->save();

        return response()->json(['success' => true]);
    }


    public function getProductMaxReturn(Request $request)
    {
        $productId = $request->input('product_id'); // Get the product ID from the request

        // Fetch the product by ID
        $product = Product::find($productId);

        if ($product) {
            return response()->json([
                'maksimum_retur' => $product->maksimum_retur,
            ]);
        }

        return response()->json(['error' => 'Product not found'], 404);
    }


    public function getTotalQuantity(Request $request)
    {
        $salesId = $request->input('sales_id');
        $productId = $request->input('product_id');

        // Fetch total quantity from sales_detail
        $salesDetail = Sales_detail::where('sales_id', $salesId)
            ->where('product_id', $productId)
            ->first();

        if ($salesDetail) {
            return response()->json([
                'total_quantity' => $salesDetail->total_quantity,
            ]);
        }

        return response()->json(['error' => 'Sales detail not found'], 404);
    }
    /**
     * Show the form for creating a new resource.
     */
        public function create()
        {
            // Fetch all customers
            $customers = Customer::all();

            // Fetch sales data to use for autofilling invoice and purchase date
            $sales = Sales::with('customer')->get(); // Assuming you have a Sale model

            // Fetch sales details to get products that can be returned
            $salesDetails = Sales_detail::with(['product', 'sales.customer'])->get();

            // Group sales by customer
            $salesByCustomer = [];
            foreach ($sales as $sale) {
                $salesByCustomer[$sale->customers_id][] = $sale;
            }

            // Prepare an array to hold products for each sale
            $productsBySale = [];
            foreach ($salesDetails as $detail) {
                if (!isset($productsBySale[$detail->sales_id])) {
                    $productsBySale[$detail->sales_id] = [];
                }
                // Include total_quantity in the product details
                $productsBySale[$detail->sales_id][] = [
                    'product_id' => $detail->product_id,
                    'product' => $detail->product, // Assuming this includes the product name
                    'total_quantity' => $detail->total_quantity // Fetch total_quantity from sales_detail
                ];
            }

            // Pass the sales details, customers, and products by sale to the view
            return view('salesRetur.create', compact('salesDetails', 'customers', 'sales', 'salesByCustomer', 'productsBySale'));
        }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate the incoming request data
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|exists:sales,id', // Ensure the sales ID is valid
            'product_id' => 'required|exists:product,id',
            'return_quantity' => 'required|integer|min:1',
            'retur_desc' => 'nullable|string',
            'refund_amount' => 'required|numeric|min:0',
        ]);

        // Fetch the sales record to get the noNota
        $sale = Sales::findOrFail($request->invoice_number); // Get the sales record by ID
        $noNota = $sale->noNota; // Get the noNota from the sales record

        // Create a new return record
        $retur = new Retur();
        $retur->customers_id = $request->customer_id;
        $retur->invoice_number = $noNota; // Store the noNota instead of sales ID
        $retur->product_id = $request->product_id;
        $retur->quantity = $request->return_quantity; // Store the return quantity
        $retur->retur_desc = $request->retur_desc; // Optional description
        $retur->status = 'Return initiated'; // Set status to "Return initiated"
        $retur->type = 'penjualan'; // Use 'penjualan' or 'pembelian' as needed
        $retur->refund_amount = $request->refund_amount; // Store the refund amount

        // Save the return record to the database
        $retur->save();

        // Redirect back with a success message
        return redirect()->route('salesRetur.index')->with('success', 'Return has been submitted successfully.');
    }


    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $retur = Retur::find($id);
            $deletedData = $retur;
            $deletedData->delete();
            return redirect()->route('salesRetur.index')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect()->route('salesRetur.index')->with('status', $msg);
        }
    }
}
