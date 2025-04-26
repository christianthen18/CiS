<?php

namespace App\Http\Controllers;

use App\Models\detailKonfigurasi;
use App\Models\Payment_Methods;
use App\Models\Product;
use App\Models\purchase;
use App\Models\Suppliers;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PurchaseController extends Controller
{
    
    public function index(Request $request)
    {
        $products = Product::all();
        $invoices = Purchase::select('noNota')->distinct()->get();
        
        $query = Purchase::with(['supplier', 'paymentMethod', 'warehouse', 'purchaseDetails.product'])
        ->whereNotNull('receive_date'); 
        
        // date filter
        if ($request->filled('start_date')) {
            $startDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->start_date)->startOfDay();
            $query->whereDate('purchase_date', '>=', $startDate);
        }
        
        if ($request->filled('end_date')) {
            $endDate = \Carbon\Carbon::createFromFormat('Y-m-d', $request->end_date)->endOfDay();
            $query->whereDate('purchase_date', '<=', $endDate);
        }
        
        // product filter
        if ($request->filled('product_id')) {
            $query->whereHas('purchaseDetails', function($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }
        
        // invoice filter
        if ($request->filled('invoice')) {
            $query->where('noNota', $request->invoice);
        }

        $query->orderBy('purchase_date', 'desc');

        $datas = $query->get();
        
        return view('purchase.index', compact('datas', 'products', 'invoices'));
    }

    public function shipping(Request $request)
    {
        $search = $request->input('search');
        $supplier_id = $request->input('supplier_id');
        $warehouse_id = $request->input('warehouse_id');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        $status = $request->input('status', 'all'); // 'pending', 'received', or 'all'
        
        $pendingQuery = Purchase::whereNull('receive_date')
            ->with(['supplier', 'paymentMethod', 'warehouse', 'purchaseDetails.product'])
            ->orderBy('purchase_date', 'asc');
            
        $receivedQuery = Purchase::whereNotNull('receive_date')
            ->with(['supplier', 'paymentMethod', 'warehouse'])
            ->orderBy('id', 'desc');
        
        // search filter
        if ($search) {
            $searchTerm = '%' . $search . '%';
            
            $pendingQuery->where(function($query) use ($searchTerm) {
                $query->where('noNota', 'like', $searchTerm)
                    ->orWhereHas('supplier', function($q) use ($searchTerm) {
                        $q->where('company_name', 'like', $searchTerm);
                    });
            });
            
            $receivedQuery->where(function($query) use ($searchTerm) {
                $query->where('noNota', 'like', $searchTerm)
                    ->orWhereHas('supplier', function($q) use ($searchTerm) {
                        $q->where('company_name', 'like', $searchTerm);
                    });
            });
        }
        
        // supplier filter
        if ($supplier_id) {
            $pendingQuery->whereHas('supplier', function($query) use ($supplier_id) {
                $query->where('id', $supplier_id);
            });
            
            $receivedQuery->whereHas('supplier', function($query) use ($supplier_id) {
                $query->where('id', $supplier_id);
            });
        }
        
        // warehouse filter
        if ($warehouse_id) {
            $pendingQuery->where('warehouse_id', $warehouse_id);
            $receivedQuery->where('warehouse_id', $warehouse_id);
        }
        
        // date filters
        if ($date_from) {
            $pendingQuery->whereDate('purchase_date', '>=', $date_from);
            $receivedQuery->whereDate('purchase_date', '>=', $date_from);
        }
        
        if ($date_to) {
            $pendingQuery->whereDate('purchase_date', '<=', $date_to);
            $receivedQuery->whereDate('purchase_date', '<=', $date_to);
        }
        
        // Get results based on status filter
        $pendingShipments = ($status == 'all' || $status == 'pending') ? $pendingQuery->get() : collect([]);
        
        if ($status == 'all' || $status == 'received') {
            $shippedOrders = ($search || $supplier_id || $warehouse_id || $date_from || $date_to) 
                ? $receivedQuery->get() 
                : $receivedQuery->take(10)->get();
        } else {
            $shippedOrders = collect([]);
        }
        
        // Get suppliers dan warehouses  dropdowns
        $suppliers = \App\Models\Suppliers::orderBy('company_name')->get();
        $warehouses = \App\Models\Warehouse::orderBy('name')->get();
        
        return view('purchase.shipping', compact(
            'pendingShipments', 
            'shippedOrders', 
            'suppliers',
            'warehouses',
            'search',
            'supplier_id',
            'warehouse_id',
            'date_from',
            'date_to',
            'status'
        ));
    }
    

    /**
     * Display the shipment detail page for a specific sale
     */
    public function shipDetail($id)
    {
        $purchase = purchase::with(['supplier', 'paymentMethod','warehouse', 'purchaseDetails.product'])->findOrFail($id);
        
        return view('purchase.ship-detail', compact('purchase'));
    }

    /**
     * Process shipping for a specific product
     */
    /**
     * Process shipping for a specific product
     */

     public function createReceiving(Request $request)
     {
         \Log::info('Receiving Request Data:', $request->all());
         
         $validatedData = $request->validate([
             'product_id' => 'required|exists:product,id',
             'purchase_id' => 'required|exists:purchase,id',
             'detail_product_id' => 'required',
             'detail_purchase_id' => 'required',
             'quantity_received' => 'required|integer|min:1',
         ]);
     
         // Get product and purchase
         $product = Product::find($validatedData['product_id']);
         $purchase = Purchase::find($validatedData['purchase_id']);
         
         // Get warehouse_id from purchase table
         $warehouseId = $purchase->warehouse_id;
         $isDirectlyInStore = !$warehouseId; // If warehouse_id is null/0, "Directly In Store"
         
         // If not "Directly In Store", check if the warehouse exists
         if (!$isDirectlyInStore) {
             $warehouseExists = DB::table('warehouse')->where('id', $warehouseId)->exists();
             if (!$warehouseExists) {
                 // Warehouse doesn't exist, so we'll treat it as "Directly In Store"
                 $isDirectlyInStore = true;
                 \Log::warning("Warehouse ID {$warehouseId} not found. Treating as 'Directly In Store'.");
             }
         }
     
         $purchaseDetail = DB::table('purchase_detail')
             ->where('product_id', $validatedData['detail_product_id'])
             ->where('purchase_id', $validatedData['detail_purchase_id'])
             ->first();
         
         if (!$purchaseDetail) {
             return redirect()->back()->with('error', 'Purchase detail not found');
         }
         
         // Get total order quantity
         $totalOrderQuantity = $purchaseDetail->quantity;
         
         // Calculate quantity already received 
         $receivedQuantity = DB::table('receive_history')
             ->where('product_id', $validatedData['detail_product_id'])
             ->where('purchase_id', $validatedData['detail_purchase_id'])
             ->sum('quantity_received') ?? 0;
         
         // Calculate remaining quantity that can be received
         $remainingQuantity = $totalOrderQuantity - $receivedQuantity;
         
         // Check that received quantity doesn't exceed remaining quantity
         if ($remainingQuantity < $validatedData['quantity_received']) {
             return redirect()->back()->with('error', 'Cannot receive more than the remaining quantity (' . $remainingQuantity . ') for this purchase item');
         }
         
         try {
             DB::beginTransaction();
             
             // Always update inventory in the main product table
             $product->stock += $validatedData['quantity_received'];
             
             // Only update product_has_warehouse if NOT "Directly In Store"
             if (!$isDirectlyInStore) {
                 $existingWarehouseStock = DB::table('product_has_warehouse')
                     ->where('product_id', $product->id)
                     ->where('warehouse_id', $warehouseId)
                     ->first();
                 
                 if ($existingWarehouseStock) {
                     // Update existing warehouse stock
                     DB::table('product_has_warehouse')
                         ->where('product_id', $product->id)
                         ->where('warehouse_id', $warehouseId)
                         ->increment('stock', $validatedData['quantity_received']);
                 } else {
                     // Insert new warehouse stock record
                     DB::table('product_has_warehouse')->insert([
                         'product_id' => $product->id,
                         'warehouse_id' => $warehouseId,
                         'stock' => $validatedData['quantity_received'],
                         'created_at' => now(),
                         'updated_at' => now()
                     ]);
                 }
             }
             
             $cogsMethod = strtolower($purchase->cogs_method ?? 'average'); // Default to average
             
             // Check if COGS method is FIFO
             if ($cogsMethod === 'fifo') {
                 // Check product_fifo table structure before insert
                 $productFifoColumns = DB::getSchemaBuilder()->getColumnListing('product_fifo');
                 \Log::info('Product FIFO columns:', $productFifoColumns);
                 
                 // Create data array for insert
                 $fifoData = [
                     'product_id' => $product->id,
                     'purchase_id' => $purchase->id,
                     'stock' => $validatedData['quantity_received'],
                 ];
                 
                 // Add warehouse_id if it's not "Directly In Store" and column exists
                 if (!$isDirectlyInStore && in_array('warehouse_id', $productFifoColumns)) {
                     $fifoData['warehouse_id'] = $warehouseId;
                 }
                 
                 // Add purchase_date if column exists
                 if (in_array('purchase_date', $productFifoColumns)) {
                     $fifoData['purchase_date'] = $purchase->purchase_date;
                 }
                 
                 // Check possible column names for price
                 $priceColumnNames = ['price', 'unit_price', 'purchase_price', 'cost', 'price_per_unit'];
                 $priceColumnName = null;
                 
                 foreach ($priceColumnNames as $columnName) {
                     if (in_array($columnName, $productFifoColumns)) {
                         $priceColumnName = $columnName;
                         break;
                     }
                 }
                 
                 // If price column found, add to data
                 if ($priceColumnName) {
                     $fifoData[$priceColumnName] = $purchaseDetail->subtotal_price / $purchaseDetail->quantity;
                 }
                 
                 if (in_array('created_at', $productFifoColumns)) {
                     $fifoData['created_at'] = now();
                 }
                 if (in_array('updated_at', $productFifoColumns)) {
                     $fifoData['updated_at'] = now();
                 }
                 
                 // Insert to product_fifo
                 DB::table('product_fifo')->insert($fifoData);
             }
             
             // Update in_order quantity (if exists)
             if (property_exists($product, 'in_order_pembelian')) {
                 $product->in_order_pembelian -= $validatedData['quantity_received'];
             }
             $product->save();
             
             // Prepare data for receive_history
             $receiveData = [
                 'purchase_id' => $validatedData['purchase_id'],
                 'product_id' => $validatedData['product_id'],
                 'quantity_received' => $validatedData['quantity_received'],
                 'received_at' => now(),
                 'created_at' => now(),
                 'updated_at' => now()
             ];
             
             // Add warehouse_id to receive_history if not "Directly In Store"
             if (!$isDirectlyInStore) {
                 $receiveData['warehouse_id'] = $warehouseId;
             }
             
             // Check if warehouse_id column exists in receive_history
             $receiveHistoryColumns = DB::getSchemaBuilder()->getColumnListing('receive_history');
             if (!in_array('warehouse_id', $receiveHistoryColumns)) {
                 // Remove warehouse_id if column doesn't exist (artinya purchase menggunakan directly in store)
                 unset($receiveData['warehouse_id']);
             }
             
             // Insert to receive_history
             DB::table('receive_history')->insert($receiveData);
             
             // Check if all items have been received
             $allDetailsFulfilledForThisPurchase = true;
             $purchaseDetails = DB::table('purchase_detail')->where('purchase_id', $purchase->id)->get();
             
             foreach ($purchaseDetails as $detail) {
                 $detailTotal = $detail->quantity;
                 
                 $detailReceived = DB::table('receive_history')
                     ->where('product_id', $detail->product_id)
                     ->where('purchase_id', $detail->purchase_id)
                     ->sum('quantity_received') ?? 0;
                 
                 if ($detailReceived < $detailTotal) {
                     $allDetailsFulfilledForThisPurchase = false;
                     break;
                 }
             }
             
             // If all items received, update receive_date in purchase
             if ($allDetailsFulfilledForThisPurchase) {
                 DB::table('purchase')
                     ->where('id', $purchase->id)
                     ->update(['receive_date' => now()]);
                 
                 DB::commit();
                 return redirect()->route('purchase.receiving')->with('success', 'All items have been received successfully. Purchase marked as completed.');
             }
     
             DB::commit();
             $locationMsg = $isDirectlyInStore ? ' to main inventory' : ' into warehouse';
             return redirect()->back()->with('success', 'Successfully received ' . $validatedData['quantity_received'] . ' units of ' . $product->name . $locationMsg);
         } catch (\Exception $e) {
             DB::rollBack();
             \Log::error('Error in receiving: ' . $e->getMessage());
             return redirect()->back()->with('error', 'Error processing reception: ' . $e->getMessage());
         }
     }
    
    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Generate invoice number
        $lastInvoice = Purchase::orderBy('id', 'desc')->first();
        $newNumber = $lastInvoice ? (int) substr($lastInvoice->noNota, 3) + 1 : 1;
    
        // Fetch suppliers, payment methods, and products
        $suppliers = Suppliers::all();
        $paymentMethods = Payment_Methods::all();
        $products = Product::all();
        $warehouses = Warehouse::all();
        
        // Get active warehouses
        $activeWarehouses = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 6)
            ->where('statusActive', 1)
            ->get();
            
        // Get warehouse options (Multi-warehouse and Directly in store)
        $warehouseOptions = DB::table('detailkonfigurasi')
            ->whereIn('id', [14, 15]) // ID 14 for Multi-warehouse, ID 15 for Directly in store
            ->where('statusActive', 1)
            ->get();
            
        $activeShippings = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 4)
            ->where('statusActive', 1)
            ->get();
            
        $activePayments = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 5)
            ->where('statusActive', 1)
            ->get();
            
        $activeCogs = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 7)
            ->where('statusActive', 1)
            ->get();
    
        return view('purchase.create', compact(
            'newNumber', 
            'suppliers', 
            'paymentMethods', 
            'warehouses', 
            'products', 
            'activeWarehouses', 
            'activeShippings', 
            'activePayments', 
            'activeCogs',
            'warehouseOptions'
        ));
    }

    public function detail($id)
    {
        $purchase = purchase::with(['supplier', 'paymentMethod', 'purchaseDetails.product']) // Eager load related data
            ->findOrFail($id); 

        // Check if supplier is null
        if (is_null($purchase->supplier)) {
            Log::info("Supplier is null for purchase ID: " . $id);
        }
        return view('purchase.detail', compact('purchase'));
    }

    public function dataKonfigurasi()
    {
        $shippings = DB::table('detailkonfigurasi')->where('konfigurasi_id', 4)->get();
        $payments = DB::table('detailkonfigurasi')->where('konfigurasi_id', 5)->get();
        $cogs = DB::table('detailkonfigurasi')->where('konfigurasi_id', 7)->get();

        // dd($discounts);

        return view('purchase.konfigurasi', compact('shippings', 'payments', 'cogs'));
    }

    public function updateConfiguration(Request $request)
    {
        // Update shippings 
        if ($request->has('shippings')) {
            $checkedShippings = $request->input('shippings', []); // Ambil pengiriman yang dipilih
            $allShippings = DB::table('detailkonfigurasi')->where('konfigurasi_id', 4)->get();

            foreach ($allShippings as $shipping) {
                if ($shipping->types === 'mandatory') {
                    // Jika mandatory, tetap aktif
                    DB::table('detailkonfigurasi')
                        ->where('id', $shipping->id)
                        ->update(['statusActive' => 1]);
                } elseif (in_array($shipping->id, $checkedShippings)) {
                    // Jika pengiriman dipilih, aktifkan
                    DB::table('detailkonfigurasi')
                        ->where('id', $shipping->id)
                        ->update(['statusActive' => 1]);
                } else {
                    // Jika pengiriman tidak dipilih, reset status menjadi 0
                    DB::table('detailkonfigurasi')
                        ->where('id', $shipping->id)
                        ->update(['statusActive' => 0]);
                }
            }
        } else {
            // Jika tidak ada pengiriman yang dipilih, reset semua status menjadi 0
            DB::table('detailkonfigurasi')->where('konfigurasi_id', 4)
                ->where('types', '!=', 'mandatory') // Hanya reset yang bukan mandatory
                ->update(['statusActive' => 0]);
        }

        // Update payments
        if ($request->has('payments')) {
            $checkedPayments = $request->input('payments', []); // Ambil pembayaran yang dipilih
            $allPayments = DB::table('detailkonfigurasi')->where('konfigurasi_id', 5)->get();

            foreach ($allPayments as $payment) {
                if ($payment->types === 'mandatory') {
                    // Jika mandatory, tetap aktif
                    DB::table('detailkonfigurasi')
                        ->where('id', $payment->id)
                        ->update(['statusActive' => 1]);
                } elseif (in_array($payment->id, $checkedPayments)) {
                    // Jika pembayaran dipilih, aktifkan
                    DB::table('detailkonfigurasi')
                        ->where('id', $payment->id)
                        ->update(['statusActive' => 1]);
                } else {
                    // Jika pembayaran tidak dipilih, reset status menjadi 0
                    DB::table('detailkonfigurasi')
                        ->where('id', $payment->id)
                        ->update(['statusActive' => 0]);
                }
            }
        } else {
            // Jika tidak ada pembayaran yang dipilih, reset semua status menjadi 0
            DB::table('detailkonfigurasi')->where('konfigurasi_id', 5)
                ->where('types', '!=', 'mandatory') // Hanya reset yang bukan mandatory
                ->update(['statusActive' => 0]);
        }

        // Update cogs 
        if ($request->has('cogs')) {
            $checkedCogs = $request->input('cogs', []); // Ambil cogs yang dipilih
            $allCogs = DB::table('detailkonfigurasi')->where('konfigurasi_id', 7)->get();

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
            // Jika tidak ada cogs yang dipilih, reset semua status menjadi 0
            DB::table('detailkonfigurasi')->where('konfigurasi_id', 7)
                ->where('types', '!=', 'mandatory') // Hanya reset yang bukan mandatory
                ->update(['statusActive' => 0]);
        }

        if ($request->has('shipping_values')) {
            foreach ($request->input('shipping_values') as $id => $value) {
                DB::table('detailkonfigurasi')
                    ->where('id', $id)
                    ->update(['value' => $value]);
            }
        }
        
        return redirect()->route("purchase.konfigurasi")->with('status', "Horray, Your konfigurasi data has been updated");

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            \Log::info('Shipping ID from request: ' . $request->shipping_id);
            
            $selectedShippingMethod = detailKonfigurasi::where('name', 'The product is shipped by the supplier')->first();
            
            \Log::info('Selected shipping method:', [
                'method' => $selectedShippingMethod ? $selectedShippingMethod->toArray() : null
            ]);
            
            $isSupplierShipping = $selectedShippingMethod && $selectedShippingMethod->value == $request->shipping_id;
            
            \Log::info('Is supplier shipping: ' . ($isSupplierShipping ? 'true' : 'false'));

    
            $noNota = 'PUR' . str_pad(DB::table('purchase')->max('id') + 1, 4, '0', STR_PAD_LEFT);
            
            $warehouseId = null;
            if ($request->input('warehouse_option') === 'multi') {
                $warehouseId = $request->input('warehouse_id');
            }
            // dd($request->input('cogs_method'));

            // Insert purchase
            $purchaseId = DB::table('purchase')->insertGetId([
                'noNota' => $noNota,
                'total_price' => $request->input('final_price'),
                'purchase_date' => $request->input('purchase_date'),
                'receive_date' => $request->input('receive_date'),
                'shipping_cost' => $request->input('shipping_cost', 0),
                'payment_methods_id' => $request->input('payment_methods_id'),
                'suppliers_id' => $request->input('supplier_id'),
                'cogs_method' => $request->input('cogs_method'),
                'warehouse_id' => $warehouseId,
            ]);
    
            $products = json_decode($request->input('products'), true);
            
            // Group by product_id to handle any potential duplicates that might still happend
            $groupedProducts = [];
            foreach ($products as $product) {
                $productId = $product['product_id'];
                if (!isset($groupedProducts[$productId])) {
                    $groupedProducts[$productId] = $product;
                } else {
                    // If the product already exists, sum the quantities
                    $groupedProducts[$productId]['quantity'] += $product['quantity'];
                    // Recalculate subtotal based on the updated quantity
                    $groupedProducts[$productId]['subtotal_price'] = $groupedProducts[$productId]['price'] * $groupedProducts[$productId]['quantity'];
                }
            }
            
            foreach ($groupedProducts as $product) {
                // Calculate subtotal price
                $subtotalPrice = $product['price'] * $product['quantity'];
                
                // Insert purchase detail
                DB::table('purchase_detail')->insert([
                    'product_id' => $product['product_id'],
                    'purchase_id' => $purchaseId,
                    'subtotal_price' => $subtotalPrice,
                    'quantity' => $product['quantity'],
                ]);
    
                // Handle warehouse stock if multi-warehouse
                if ($request->input('warehouse_option') === 'multi' && $warehouseId) {
                    $existingRecord = DB::table('product_has_warehouse')
                        ->where('product_id', $product['product_id'])
                        ->where('warehouse_id', $warehouseId)
                        ->first();
    
                    if ($existingRecord) {
                        DB::table('product_has_warehouse')
                            ->where('product_id', $product['product_id'])
                            ->where('warehouse_id', $warehouseId)
                            ->increment('stock', $product['quantity']);
                    } else {
                        DB::table('product_has_warehouse')->insert([
                            'product_id' => $product['product_id'],
                            'warehouse_id' => $warehouseId,
                            'stock' => $product['quantity']
                        ]);
                    }
                }
            }
            if (!$isSupplierShipping) {
                // Update inventory with the selected COGS method
                $purchase = Purchase::find($purchaseId);
                $purchase->updateInventory($request->input('cogs_method'), array_values($groupedProducts));
            }
    
            if ($isSupplierShipping) {
                foreach ($groupedProducts as $product) {
                    $purchasedProduct = Product::find($product['product_id']);
                    $purchasedProduct->in_order_pembelian += $product['quantity'];
                    $purchasedProduct->save();
                }
                
                return redirect()->route('purchase.receiving');
            }
    
            return redirect()->route('purchase.index')->with('success', 'Purchase has been created successfully');
        
        } catch (\Exception $e) {
            \Log::error('Purchase creation error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Failed to create purchase: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $purchase = Purchase::with(['supplier', 'paymentMethod', 'purchaseDetails.product'])->findOrFail($id);
        return view('purchase.detail', compact('purchase'));
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
        //
    }
}
