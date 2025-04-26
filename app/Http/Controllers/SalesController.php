<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\detailKonfigurasi;
use App\Models\Employe;
use App\Models\Payment_Methods;
use App\Models\Product;
use App\Models\Sales;
use App\Models\Sales_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Auth;

class SalesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $products = Product::all();
        $invoices = Sales::select('noNota')->distinct()->get();
    
        $query = Sales::with(['customer', 'paymentMethod', 'salesDetail.product'])
            ->whereNotNull('shipped_date'); 
    
        // filter
        if ($request->filled('start_date')) {
            try {
                // Handle different date formats (both Y-m-d and d/m/Y)
                $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
                $query->whereDate('date', '>=', $startDate);
            } catch (\Exception $e) {
                // Log error or handle invalid date format
            }
        }
    
        if ($request->filled('end_date')) {
            try {
                // Handle different date formats (both Y-m-d and d/m/Y)
                $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
                $query->whereDate('date', '<=', $endDate);
            } catch (\Exception $e) {
                // Log error or handle invalid date format
            }
        }
    
        // product filter
        if ($request->filled('product_id')) {
            $query->whereHas('salesDetail', function($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }
    
        // invoice filter
        if ($request->filled('invoice')) {
            $query->where('noNota', $request->invoice);
        }
    
        // Order by sales date, newest first
        $query->orderBy('date', 'desc');
    
        $datas = $query->get();
    
        return view('sales.index', compact('datas', 'products', 'invoices'));
    }

    /**
     * Display the list of sales that need shipping
     */
    public function shipping(Request $request)
    {
        $search = $request->input('search');
        $customer_id = $request->input('customer_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $shipping_type = $request->input('shipping_type', 'all'); // 'pending', 'shipped', or 'all'
        
        $pendingQuery = Sales::whereNull('shipped_date')
            ->with(['customer', 'paymentMethod', 'salesDetail.product'])
            ->orderBy('date', 'asc');
            
        $shippedQuery = Sales::whereNotNull('shipped_date')
            ->with(['customer', 'paymentMethod'])
            ->orderBy('id', 'desc');
        
        // search filter
        if ($search) {
            $searchTerm = '%' . $search . '%';
            
            $pendingQuery->where(function($query) use ($searchTerm) {
                $query->where('noNota', 'like', $searchTerm)
                    ->orWhereHas('customer', function($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
            });
            
            $shippedQuery->where(function($query) use ($searchTerm) {
                $query->where('noNota', 'like', $searchTerm)
                    ->orWhereHas('customer', function($q) use ($searchTerm) {
                        $q->where('name', 'like', $searchTerm);
                    });
                
                if (Schema::hasColumn('sales', 'shipping_address')) {
                    $query->orWhere('shipping_address', 'like', $searchTerm);
                }
            });
        }
        
        // customer filter
        if ($customer_id) {
            $pendingQuery->whereHas('customer', function($query) use ($customer_id) {
                $query->where('id', $customer_id);
            });
            
            $shippedQuery->whereHas('customer', function($query) use ($customer_id) {
                $query->where('id', $customer_id);
            });
        }
        
        // date filters
        if ($date_from) {
            $pendingQuery->whereDate('date', '>=', $date_from);
            $shippedQuery->whereDate('date', '>=', $date_from);
        }
        
        if ($date_to) {
            $pendingQuery->whereDate('date', '<=', $date_to);
            $shippedQuery->whereDate('date', '<=', $date_to);
        }
        
        // Get results
        $pendingShipments = ($shipping_type == 'all' || $shipping_type == 'pending') ? $pendingQuery->get() : collect([]);
        
        if ($shipping_type == 'all' || $shipping_type == 'shipped') {
            $shippedOrders = ($search || $customer_id || $date_from || $date_to) 
                ? $shippedQuery->get() 
                : $shippedQuery->take(10)->get();
        } else {
            $shippedOrders = collect([]);
        }
        
        // customers dropdown
        $customers = Customer::where('status_active', 1)->orderBy('name')->get();
        
        return view('sales.shipping', compact(
            'pendingShipments', 
            'shippedOrders', 
            'customers',
            'search',
            'customer_id',
            'date_from',
            'date_to',
            'shipping_type'
        ));
    }

    /**
     * Display the shipment detail page for a specific sale
     */
    public function shipDetail($id)
    {
        // Load sale with related data
        $sale = Sales::with(['customer', 'paymentMethod', 'salesDetail.product'])->findOrFail($id);
        
        return view('sales.ship-detail', compact('sale'));
    }

    /**
     * Process shipping for a specific product
     */
    /**
     * Process shipping for a specific product
     */
    public function createShipping(Request $request)
    {
        //\Log::info('Shipping Request Data:', $request->all());
        
        $validatedData = $request->validate([
            'product_id' => 'required|exists:product,id',
            'sale_id' => 'required|exists:sales,id',
            'detail_product_id' => 'required',
            'detail_sales_id' => 'required',
            'quantity_shipped' => 'required|integer|min:1',
            'shipping_address' => 'nullable|string',
            'recipients_name' => 'nullable|string',
        ]);
    
        // Get product and sale
        $product = Product::find($validatedData['product_id']);
        $sale = Sales::find($validatedData['sale_id']);
        
        // Get sales detail
        $salesDetail = DB::table('sales_detail')
            ->where('product_id', $validatedData['detail_product_id'])
            ->where('sales_id', $validatedData['detail_sales_id'])
            ->first();
        
        if (!$salesDetail) {
            return redirect()->back()->with('error', 'Sales detail not found');
        }
        
        // Cek stok mencukupi
        if ($product->stock < $validatedData['quantity_shipped']) {
            return redirect()->back()->with('error', 'Insufficient stock for ' . $product->name);
        }
        
        // Cek in_order mencukupi
        if (($product->in_order_penjualan ?? 0) < $validatedData['quantity_shipped']) {
            return redirect()->back()->with('error', 'Cannot ship more than total in-order quantity for ' . $product->name);
        }
        
        // Ambil total order quantity untuk detail ini
        $totalOrderQuantity = $salesDetail->total_quantity;
        
        // Hitung jumlah yang sudah dikirim untuk detail ini 
        $shippedQuantity = DB::table('shipping_history')
            ->where('product_id', $validatedData['detail_product_id'])
            ->where('sales_id', $validatedData['detail_sales_id'])
            ->sum('quantity_shipped') ?? 0;
        
        // Hitung sisa yang bisa dikirim
        $remainingQuantity = $totalOrderQuantity - $shippedQuantity;
        
        // Cek kuantitas yang dikirim tidak melebihi sisa
        if ($remainingQuantity < $validatedData['quantity_shipped']) {
            return redirect()->back()->with('error', 'Cannot ship more than the remaining quantity (' . $remainingQuantity . ') for this invoice item');
        }
        
        $cogsMethodId = $sale->cogs_method ?? 19; // Default to S-Average (19)
        
        $cogsMethod = 'average'; 
        if ($cogsMethodId == 16 || $cogsMethodId == 18) { 
            $cogsMethod = 'fifo';
        }
    
        //\log::info('Using COGS method: ' . $cogsMethod . ' from ID: ' . $cogsMethodId);
        
        try {
            DB::beginTransaction();
            
            if ($cogsMethod === 'fifo') {
                // FIFO method - reduce stock from tabel product dan product_fifo 
                //\log::info('Processing with FIFO method');
                $productFifoEntries = DB::table('product_fifo')
                    ->where('product_id', $product->id)
                    ->where('stock', '>', 0)
                    ->orderBy('purchase_date', 'asc')
                    ->get();
    
                //\log::info('Found ' . $productFifoEntries->count() . ' FIFO entries for product: ' . $product->id);
                
                $remainingToReduce = $validatedData['quantity_shipped'];
                
                // Reduce stock in FIFO order
                foreach ($productFifoEntries as $fifoEntry) {
                    if ($remainingToReduce <= 0) break;
                    
                    $reduceQuantity = min($fifoEntry->stock, $remainingToReduce);
                    
                    //\log::info('Reducing ' . $reduceQuantity . ' from FIFO entry ID: ' . $fifoEntry->id);
                    
                    DB::table('product_fifo')
                        ->where('id', $fifoEntry->id)
                        ->update(['stock' => $fifoEntry->stock - $reduceQuantity]);
                    
                    $remainingToReduce -= $reduceQuantity;
                }
                
                if ($remainingToReduce > 0) {
                    //\log::warning('Not enough FIFO entries to reduce ' . $validatedData['quantity_shipped'] . ' units for product ' . $product->id);
                }
                
                // reduce from main product stock
                $product->stock -= $validatedData['quantity_shipped'];
            } else {
                // Average method - only reduce rabel product
                //\log::info('Processing with Average method');
                $product->stock -= $validatedData['quantity_shipped'];
            }
            
            // Update in_order quantity
            $product->in_order_penjualan -= $validatedData['quantity_shipped'];
            $product->save();
            
            // Ambil alamat pengiriman dan nama penerima
            $shippingAddress = $validatedData['shipping_address'] ?? null;
            $recipientsName = $validatedData['recipients_name'] ?? null;
            
            // Jika kosong, gunakan data dari customer
            if (empty($shippingAddress) && $sale->customer) {
                $shippingAddress = $sale->customer->address;
            }
            
            if (empty($recipientsName) && $sale->customer) {
                $recipientsName = $sale->customer->name;
            }
            
            // Insert ke shipping_history
            DB::table('shipping_history')->insert([
                'sales_id' => $validatedData['sale_id'],
                'product_id' => $validatedData['product_id'],
                'quantity_shipped' => $validatedData['quantity_shipped'],
                'shipping_address' => $shippingAddress,
                'recipients_name' => $recipientsName,
                'shipped_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
                'sales_detail_id' => $salesDetail->id ?? null,
            ]);
            
            // Cek apakah semua item telah dikirim
            $allDetailsFulfilledForThisInvoice = true;
            $salesDetails = DB::table('sales_detail')->where('sales_id', $sale->id)->get();
            
            foreach ($salesDetails as $detail) {
                $detailTotal = $detail->total_quantity;
                
                $detailShipped = DB::table('shipping_history')
                    ->where('product_id', $detail->product_id)
                    ->where('sales_id', $detail->sales_id)
                    ->sum('quantity_shipped') ?? 0;
                
                if ($detailShipped < $detailTotal) {
                    $allDetailsFulfilledForThisInvoice = false;
                    break;
                }
            }
            
            // Jika semua item dikirim, update shipped_date di sales
            if ($allDetailsFulfilledForThisInvoice) {
                DB::table('sales')
                    ->where('id', $sale->id)
                    ->update(['shipped_date' => now()]);
                
                DB::commit();
                return redirect()->route('sales.shipping')->with('success', 'All items have been shipped successfully. Order marked as completed.');
            }
    
            DB::commit();
            return redirect()->back()->with('success', 'Successfully shipped ' . $validatedData['quantity_shipped'] . ' units of ' . $product->name);
        } catch (\Exception $e) {
            DB::rollBack();
            //\log::error('Error in shipping: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error processing shipment: ' . $e->getMessage());
        }
    }
    
    /**
     * Ship all available items in a sale
     */
    public function shipAll(Request $request, $id)
    {
        $request->validate([
            'shipped_date' => 'required|date',
        ]);
        
        // Get the sale with its details
        $sale = Sales::with('salesDetail.product')->findOrFail($id);
        
        // Process each product in the sale
        $someItemsShipped = false;
        
        foreach ($sale->salesDetail as $detail) {
            $product = Product::find($detail->product_id);
            
            if (!$product) continue;
            
            // Get total order quantity for this detail
            $totalOrderQuantity = $detail->total_quantity;
            
            // Calculate how much has been shipped already for this sales detail
            $shippedQuantity = DB::table('shipping_history')
                ->where('sales_detail_id', $detail->sales_id)
                ->sum('quantity_shipped') ?? 0;
            
            // Calculate remaining quantity for this sales detail
            $remainingQuantity = $totalOrderQuantity - $shippedQuantity;
            
            // Skip if nothing remains to be shipped for this detail
            if ($remainingQuantity <= 0) {
                continue;
            }
            
            // Get the total in_order quantity
            $totalInOrder = $product->in_order_penjualan ?? 0;
            
            // Calculate quantity to ship (min between remaining for this detail, total in order, and available stock)
            $quantityToShip = min($remainingQuantity, $totalInOrder, $product->stock);
            
            if ($quantityToShip <= 0) continue;
            
            $someItemsShipped = true;
            
            // Get the COGS method used for this sale
            $cogsMethodId = $sale->cogs_method_id;
            $cogsMethod = 'fifo'; // Default to fifo
            
            if ($cogsMethodId) {
                // Get the COGS method name from detailkonfigurasi
                $cogsMethodName = DB::table('detailkonfigurasi')
                    ->where('id', $cogsMethodId)
                    ->value('name');
                
                // Check if name contains 'fifo' (case-insensitive)
                if ($cogsMethodName && stripos($cogsMethodName, 'fifo') !== false) {
                    $cogsMethod = 'fifo';
                } else if ($cogsMethodName) {
                    $cogsMethod = 'average';
                }
            }
            
            if ($cogsMethod === 'fifo') {
                // FIFO method - reduce stock from both product and product_fifo tables
                $productFifoEntries = DB::table('product_fifo')
                    ->where('product_id', $product->id)
                    ->where('stock', '>', 0)
                    ->orderBy('purchase_date', 'asc')
                    ->get();
    
                $remainingQuantity = $quantityToShip;
                
                // Reduce stock in FIFO order
                foreach ($productFifoEntries as $fifoEntry) {
                    if ($remainingQuantity <= 0) break;
                    
                    $reduceQuantity = min($fifoEntry->stock, $remainingQuantity);
                    
                    DB::table('product_fifo')
                        ->where('id', $fifoEntry->id)
                        ->update(['stock' => $fifoEntry->stock - $reduceQuantity]);
                    
                    $remainingQuantity -= $reduceQuantity;
                }
            }
            
            // Record this shipment in shipping_history
            DB::table('shipping_history')->insert([
                'sales_id' => $sale->id,
                'sales_detail_id' => $detail->id,
                'product_id' => $product->id,
                'quantity_shipped' => $quantityToShip,
                'shipped_at' => $request->shipped_date,
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            // Update product stock (for both FIFO and Average methods)
            $product->stock -= $quantityToShip;
            $product->in_order_penjualan -= $quantityToShip;
            $product->save();
        }
        
        // If no items were shipped, return with error
        if (!$someItemsShipped) {
            return redirect()->back()->with('error', 'No items could be shipped. Check stock availability.');
        }
        
        // Check if all items in this invoice have been fully shipped
        $allDetailsFulfilledForThisInvoice = true;
        
        foreach ($sale->salesDetail as $detail) {
            $detailTotal = $detail->total_quantity;
            $detailShipped = DB::table('shipping_history')
                ->where('sales_detail_id', $detail->id)
                ->sum('quantity_shipped') ?? 0;
            
            if ($detailShipped < $detailTotal) {
                $allDetailsFulfilledForThisInvoice = false;
                break;
            }
        }
        
        // If all items are shipped, update the sale's shipped_date
        if ($allDetailsFulfilledForThisInvoice) {
            $sale->shipped_date = $request->shipped_date;
            $sale->save();
            
            return redirect()->route('sales.shipping')->with('success', 'All items have been shipped successfully. Order marked as completed.');
        } else {
            // If some items are still to be shipped, update the message
            return redirect()->back()->with('success', 'Shipped all available items. Some items are still pending.');
        }
    }
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Ambil nomor nota terakhir dari database
        $lastInvoice = Sales::orderBy('noNota', 'desc')->first();
        $newNumber = $lastInvoice ? (int) substr($lastInvoice->noNota, 3) + 1 : 1;

        // Ambil semua pelanggan, metode pembayaran, produk, dan karyawan
        $customers = Customer::all();
        $paymentMethods = Payment_Methods::all();
        $products = Product::all();
        $employees = Employe::all();

        $activeDiscounts = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 1)
            ->where('statusActive', 1)
            ->get();

        $activeShippings = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 2)
            ->where('statusActive', 1)
            ->get();

        $activePayments = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 3)
            ->where('statusActive', 1)
            ->get();

        $activeCogs = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 8)
            ->where('statusActive', 1)
            ->get();

        $user = Auth::user();
    
        $employeId = Employe::where('users_id', $user->id)->value('id');
        
        return view('sales.create', compact('newNumber', 'customers', 'paymentMethods', 'products', 'employees', 'activeDiscounts', 'activeShippings', 'activePayments', 'activeCogs', 'employeId'));

    }

    public function createReturn()
    {
        // Fetch all customers
        $customers = Customer::all();

        $sales = Sales::with('customer')->get();

        // Fetch sales details to get products that can be returned
        $salesDetails = Sales_detail::with(['product', 'sales.customer'])->get();

        // Group sales by customer
        $salesByCustomer = [];
        foreach ($sales as $sale) {
            $salesByCustomer[$sale->customers_id][] = $sale;
        }

        // array to hold products for each sale
        $productsBySale = [];
        foreach ($salesDetails as $detail) {
            if (!isset($productsBySale[$detail->sales_id])) {
                $productsBySale[$detail->sales_id] = [];
            }

            $productsBySale[$detail->sales_id][] = [
                'product_id' => $detail->product_id,
                'product' => $detail->product, 
                'total_quantity' => $detail->total_quantity // Fetch total_quantity from sales_detail
            ];
        }

        return view('sales.retur', compact('salesDetails', 'customers', 'salesByCustomer', 'productsBySale'));
    }

    // public function returPenjualan()
    // {

    // }

    public function detail($id)
    {
        $sale = Sales::with(['customer', 'paymentMethod', 'salesDetail.product']) // Eager load related data
            ->findOrFail($id); // Fetch the sale or fail if not found

        return view('sales.detail', compact('sale'));
    }

    public function dataKonfigurasi()
    {
        $discounts = DB::table('detailkonfigurasi')->where('konfigurasi_id', 1)->get();
        $shippings = DB::table('detailkonfigurasi')->where('konfigurasi_id', 2)->get();
        $payments = DB::table('detailkonfigurasi')->where('konfigurasi_id', 3)->get();
        $cogs = DB::table('detailkonfigurasi')->where('konfigurasi_id', 8)->get();

        // dd($discounts);

        return view('sales.konfigurasi', compact('discounts', 'shippings', 'payments', 'cogs'));
    }

    public function updateConfiguration(Request $request)
    {
        // Update discounts
        if ($request->has('discounts')) {
            $allDiscounts = DB::table('detailkonfigurasi')->where('konfigurasi_id', 1)->get();
    
            // update status
            foreach ($allDiscounts as $discount) {
                if ($request->has('discounts') && in_array($discount->id, $request->input('discounts', []))) {
                    DB::table('detailkonfigurasi')
                        ->where('id', $discount->id)
                        ->update(['statusActive' => 1]);
                } else {
                    DB::table('detailkonfigurasi')
                        ->where('id', $discount->id)
                        ->update(['statusActive' => 0]);
                }
            }
        } else {
            DB::table('detailkonfigurasi')->where('konfigurasi_id', 1)
                ->where('types', '!=', 'mandatory') // Hanya reset yang bukan mandatory
                ->update(['statusActive' => 0]);
        }
    
        // Update shippings 
        if ($request->has('shippings')) {
            $allShippings = DB::table('detailkonfigurasi')->where('konfigurasi_id', 2)->get();
    
            foreach ($allShippings as $shipping) {
                if ($shipping->types === 'mandatory') {
                    DB::table('detailkonfigurasi')
                        ->where('id', $shipping->id)
                        ->update(['statusActive' => 1]);
                } elseif (in_array($shipping->id, $request->input('shippings', []))) {
                    DB::table('detailkonfigurasi')
                        ->where('id', $shipping->id)
                        ->update(['statusActive' => 1]);
                } else {
                    DB::table('detailkonfigurasi')
                        ->where('id', $shipping->id)
                        ->update(['statusActive' => 0]);
                }
            }
        } else {
            DB::table('detailkonfigurasi')->where('konfigurasi_id', 2)
                ->where('types', '!=', 'mandatory') // Hanya reset yang bukan mandatory
                ->update(['statusActive' => 0]);
        }
    
    
        // Update payments
        if ($request->has('payments')) {
            $allPayments = DB::table('detailkonfigurasi')->where('konfigurasi_id', 3)->get();
    
            foreach ($allPayments as $payment) {
                if ($payment->types === 'mandatory') {
                    DB::table('detailkonfigurasi')
                        ->where('id', $payment->id)
                        ->update(['statusActive' => 1]);
                } elseif (in_array($payment->id, $request->input('payments', []))) {
                    DB::table('detailkonfigurasi')
                        ->where('id', $payment->id)
                        ->update(['statusActive' => 1]);
                } else {
                    DB::table('detailkonfigurasi')
                        ->where('id', $payment->id)
                        ->update(['statusActive' => 0]);
                }
            }
        } else {
            // Jika tidak ada pembayaran yang dipilih, reset semua status menjadi 0
            DB::table('detailkonfigurasi')->where('konfigurasi_id', 3)
                ->where('types', '!=', 'mandatory') // Hanya reset yang bukan mandatory
                ->update(['statusActive' => 0]);
        }
    
        // Update cogs 
        if ($request->has('cogs')) {
            $checkedCogs = $request->input('cogs', []); // Ambil cogs yang dipilih
            $allCogs = DB::table('detailkonfigurasi')->where('konfigurasi_id', 8)->get();
    
            foreach ($allCogs as $cogs_method) {
                if ($cogs_method->types === 'mandatory') {
                    // Jika mandatory, tetap aktif
                    DB::table('detailkonfigurasi')
                        ->where('id', $cogs_method->id)
                        ->update(['statusActive' => 1]);
                } elseif (in_array($cogs_method->id, $checkedCogs)) {
                    // Jika cogs dipilih, aktifkan
                    DB::table('detailkonfigurasi')
                        ->where('id', $cogs_method->id)
                        ->update(['statusActive' => 1]);
                } else {
                    // Jika cogs tidak dipilih, reset status menjadi 0
                    DB::table('detailkonfigurasi')
                        ->where('id', $cogs_method->id)
                        ->update(['statusActive' => 0]);
                }
            }
        } else {
            // Jika tidak ada pengiriman yang dipilih, reset semua status menjadi 0
            DB::table('detailkonfigurasi')->where('konfigurasi_id', 8)
                ->where('types', '!=', 'mandatory') // Hanya reset yang bukan mandatory
                ->update(['statusActive' => 0]);
        }
    
        // Update discount values
        if ($request->has('discount_values')) {
            foreach ($request->input('discount_values') as $id => $value) {
                DB::table('detailkonfigurasi')
                    ->where('id', $id)
                    ->update(['value' => $value]);
            }
        }
    
        // Update discount minimum values
        if ($request->has('discount_min_values')) {
            foreach ($request->input('discount_min_values') as $id => $min_value) {
                DB::table('detailkonfigurasi')
                    ->where('id', $id)
                    ->update(['min_value' => $min_value]);
            }
        }
    
        // Update shipping values
        if ($request->has('shipping_values')) {
            foreach ($request->input('shipping_values') as $id => $value) {
                DB::table('detailkonfigurasi')
                    ->where('id', $id)
                    ->update(['value' => $value]);
            }
        }
    
        return redirect()->route("sales.konfigurasi")->with('status', "Horray, Your konfigurasi data has been updated");
    }


    public function store(Request $request)
    {
        try {
            // Validate products before starting transaction
            $products = json_decode($request->input('products'), true);
            
            if (empty($products)) {
                return redirect()->back()->with('error', 'No products provided for the sale')->withInput();
            }
            
            // Check stock availability and validate quantities
            foreach ($products as $product) {
                // Ensure quantity is positive
                if (!isset($product['quantity']) || $product['quantity'] <= 0) {
                    return redirect()->back()
                        ->with('error', 'All product quantities must be positive')
                        ->withInput();
                }
                
                // Check available stock
                $productItem = Product::find($product['product_id']);
                if (!$productItem) {
                    return redirect()->back()
                        ->with('error', 'Product not found')
                        ->withInput();
                }
                
                // If requested quantity exceeds available stock
                if ($product['quantity'] > $productItem->stock) {
                    return redirect()->back()
                        ->with('error', "Insufficient stock for {$productItem->name}. Available: {$productItem->stock}")
                        ->withInput();
                }
            }

            // Start database transaction
            DB::beginTransaction();

            try {
                $cogsMethodMap = [
                    '18' => 'fifo',
                    '19' => 'average'
                ];

                $cogsMethod = $cogsMethodMap[$request->input('cogs_method')] ?? null;

                if (!$cogsMethod) {
                    throw new \Exception('Invalid COGS method selected');
                }

                // Generate invoice number
                $lastId = DB::table('sales')->max('id') ?? 0;
                $noNota = 'INV' . str_pad($lastId + 1, 4, '0', STR_PAD_LEFT);

                // Create the sale record
                $sale = Sales::create([
                    'noNota' => $noNota,
                    'total_price' => $request->input('final_price'),
                    'date' => $request->input('sales_date'),
                    'shipped_date' => $request->input('sales_shipdate'),
                    'employes_id' => $request->input('sales_employes_id'),
                    'payment_methods_id' => $request->input('payment_methods_id'),
                    'card_number' => $request->input('card_number'),
                    'customers_id' => $request->input('sales_cust_id'),
                    'shipping_cost' => $request->input('shipping_cost', 0),
                    'discount' => $request->input('sales_disc', 0),
                    'cogs_method' => $request->input('cogs_method'),
                ]);
                // dd($sale);
                // Process products
                $products = json_decode($request->input('products'), true);

                if (empty($products)) {
                    throw new \Exception('No products provided for the sale');
                }

                // Insert sales details 
                $productsForInventory = [];
                foreach ($products as $product) {
                    $quantity = max(1, intval($product['quantity']));

                    // Insert into sales_detail
                    DB::table('sales_detail')->insert([
                        'product_id' => $product['product_id'],
                        'sales_id' => $sale->id,
                        'total_quantity' => $product['quantity'],
                        'total_price' => $product['price'] * $product['quantity'],
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // Prepare product data for inventory update
                    $productsForInventory[] = [
                        'product_id' => $product['product_id'],
                        'quantity' => $product['quantity']
                    ];
                }

                // Check the selected shipping method
                $selectedShippingMethod = detailKonfigurasi::where('name', 'Products are sent by store delivery service')->first();
                $isDeliveryService = $selectedShippingMethod && $selectedShippingMethod->value == $request->shipping_id;

                // Update inventory only if the shipping method is NOT the delivery service
                if (!$isDeliveryService) {
                    // Update inventory using the model updateInventory method
                    $sale->updateInventory($cogsMethod, $productsForInventory);
                }

                // Commit transaction
                DB::commit();

                // Handle shipping logic
                if ($isDeliveryService) {
                    foreach ($products as $product) {
                        $purchasedProduct = Product::find($product['product_id']);
                        $purchasedProduct->in_order_penjualan += $product['quantity'];
                        $purchasedProduct->save();
                    }
                    return redirect()->route('sales.shipping');
                }

                return redirect()->route('sales.index')->with('success', 'Sale has been created successfully');

            } catch (\Exception $e) {
                DB::rollBack();
                //\log::error('Failed to create sale: ' . $e->getMessage());
                throw $e;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()
                ->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', 'Failed to create sale. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $data = Sales::find($id);
        return view("sales.edit", compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sales $sales)
    {
        $updatedData = $sales;
        $updatedData->noNota = $request->noNota;
        $updatedData->total_price = $request->total_price;
        $updatedData->date = $request->date;
        $updatedData->shipped_date = $request->shipped_date;
        $updatedData->shipping_cost = $request->shipping_cost;
        $updatedData->discount = $request->discount;

        $updatedData->save();


        return redirect()->route("sales.index")->with('status', "Horray, Your transaction data is already updated");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // //\log::info('Deleting sales record:', ['id' => $sales->id]);

        try {
            $sales = Sales::find($id);
            $deletedData = $sales;
            $deletedData->delete();
            return redirect()->route('sales.index')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect()->route('sales.index')->with('status', $msg);
        }
    }
}
