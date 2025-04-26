<?php

namespace App\Http\Controllers;

use App\Models\Categories;
use App\Models\detailKonfigurasi;
use App\Models\Product;
use App\Models\Product_Image;
use App\Models\ProductFifo;
use App\Models\purchase;
use App\Models\Suppliers;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $category_id = $request->input('category_id');
        $min_price = $request->input('min_price');
        $max_price = $request->input('max_price');
        $brand = $request->input('brand'); // New parameter for brand search
        $sort_by = $request->input('sort_by', 'name');
        $sort_dir = $request->input('sort_dir', 'asc');
    
        $query = Product::with('productImage')
            ->where('status_active', 1);
        
        // Apply search filter for name and description
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                ->orWhere('desc', 'like', '%' . $search . '%')
                ->orWhere('brands', 'like', '%' . $search . '%'); // Also search in brands
            });
        }
        
        // Apply specific brand filter
        if ($brand) {
            $query->where('brands', 'like', '%' . $brand . '%');
        }
    
        // Apply category filter
        if ($category_id) {
            $query->where('category_id', $category_id);
        }
    
        // Apply price range filters
        if ($min_price) {
            $query->where('price', '>=', $min_price);
        }
    
        if ($max_price) {
            $query->where('price', '<=', $max_price);
        }
    
        // Apply sorting
        $query->orderBy($sort_by, $sort_dir);
    
        $products = $query->get();
        $categories = \App\Models\Categories::all();
        
        // Get unique brands for the dropdown
        $brands = Product::where('status_active', 1)
            ->whereNotNull('brands')
            ->where('brands', '!=', '')
            ->orderBy('brands')
            ->pluck('brands')
            ->unique();
    
        return view('product.index', [
            'datas' => $products,
            'categories' => $categories,
            'brands' => $brands,
            'search' => $search,
            'brand' => $brand,
            'category_id' => $category_id,
            'min_price' => $min_price,
            'max_price' => $max_price,
            'sort_by' => $sort_by,
            'sort_dir' => $sort_dir
        ]);
    }

    public function salesProfitLoss(Request $request)
    {
        // Get search parameters
        $searchProduct = $request->input('search_product');
        $searchInvoice = $request->input('search_invoice');
        
        // Get all sales with their details
        $salesQuery = DB::table('sales')
            ->select('sales.id', 'sales.noNota', 'sales.date', 'sales.total_price as invoice_total', 
                     'sales.discount', 'sales.shipping_cost', 'sales.cogs_method')
            ->where('sales.deleted_at', null);
        
        // Apply invoice search if provided
        if ($searchInvoice) {
            $salesQuery->where('sales.noNota', 'like', '%' . $searchInvoice . '%');
        }
        
        // If searching by product, we need to join with sales_detail
        if ($searchProduct) {
            $salesQuery->join('sales_detail', 'sales.id', '=', 'sales_detail.sales_id')
                      ->join('product', 'product.id', '=', 'sales_detail.product_id')
                      ->where('product.name', 'like', '%' . $searchProduct . '%')
                      ->distinct(); // Ensure we don't get duplicate sales
        }
        
        $sales = $salesQuery->orderBy('sales.date', 'desc')->get();
        
        $totalRevenue = 0;
        $totalCost = 0;
        $totalProfit = 0;
        $salesResults = [];
        
        foreach ($sales as $sale) {
            // Get details for this sale
            $saleDetails = DB::table('sales_detail')
                ->join('product', 'product.id', '=', 'sales_detail.product_id')
                ->select(
                    'sales_detail.product_id', 
                    'product.name as product_name',
                    'product.cogs_methods',
                    'sales_detail.total_quantity',
                    'sales_detail.total_price as item_revenue',
                    'product.cost as standard_cost'
                )
                ->where('sales_detail.sales_id', $sale->id)
                ->where('sales_detail.deleted_at', null);
                
            // Filter by product name if searching by product
            if ($searchProduct) {
                $saleDetails->where('product.name', 'like', '%' . $searchProduct . '%');
            }
            
            $saleDetails = $saleDetails->get();
            
            // Skip sales with no matching products when filtering by product
            if ($searchProduct && $saleDetails->isEmpty()) {
                continue;
            }
            
            $saleRevenue = 0;
            $saleCost = 0;
            $saleProfit = 0;
            $itemsData = [];
            
            // Calculate profit for each item in the sale
            foreach ($saleDetails as $detail) {
                $effectiveCost = 0;
                $revenue = $detail->item_revenue;
                
                // Calculate cost based on COGS method
                if ($detail->cogs_methods === 'fifo') {
                    // For FIFO, get the weighted average cost of inventory at time of sale
                    $fifoRecords = DB::table('product_fifo')
                        ->where('product_id', $detail->product_id)
                        ->where('purchase_date', '<', $sale->date) // Only consider inventory available at sale time
                        ->whereNull('deleted_at')
                        ->orderBy('purchase_date', 'asc')
                        ->select('price', 'stock')
                        ->get();
                    
                    $totalValue = 0;
                    $totalQuantity = 0;
                    
                    foreach ($fifoRecords as $record) {
                        $totalValue += $record->price * $record->stock;
                        $totalQuantity += $record->stock;
                    }
                    
                    $avgCost = $totalQuantity > 0 ? $totalValue / $totalQuantity : $detail->standard_cost;
                    $effectiveCost = $avgCost * $detail->total_quantity;
                } else {
                    // For average method, use the standard cost
                    $effectiveCost = $detail->standard_cost * $detail->total_quantity;
                }
                
                $itemProfit = $revenue - $effectiveCost;
                $profitMargin = $revenue > 0 ? ($itemProfit / $revenue) * 100 : 0;
                
                // Accumulate for the sale
                $saleRevenue += $revenue;
                $saleCost += $effectiveCost;
                
                // Add to items array
                $itemsData[] = [
                    'product_id' => $detail->product_id,
                    'product_name' => $detail->product_name,
                    'quantity' => $detail->total_quantity,
                    'revenue' => $revenue,
                    'cost' => $effectiveCost,
                    'profit' => $itemProfit,
                    'profit_margin' => $profitMargin
                ];
            }
            
            // Account for discounts and shipping costs
            $saleProfit = $saleRevenue - $saleCost - ($sale->discount ?? 0);
            
            // Add to overall totals
            $totalRevenue += $saleRevenue;
            $totalCost += $saleCost;
            $totalProfit += $saleProfit;
            
            // Add this sale to the results
            $salesResults[] = [
                'id' => $sale->id,
                'invoice_number' => $sale->noNota,
                'date' => $sale->date,
                'revenue' => $saleRevenue,
                'cost' => $saleCost,
                'discount' => $sale->discount,
                'shipping_cost' => $sale->shipping_cost,
                'profit' => $saleProfit,
                'profit_margin' => $saleRevenue > 0 ? ($saleProfit / $saleRevenue) * 100 : 0,
                'items' => $itemsData
            ];
        }
        
        // Calculate product-wise profit (grouping by product)
        $productProfits = [];
        foreach ($salesResults as $sale) {
            foreach ($sale['items'] as $item) {
                $productId = $item['product_id'];
                
                if (!isset($productProfits[$productId])) {
                    $productProfits[$productId] = [
                        'product_id' => $productId,
                        'product_name' => $item['product_name'],
                        'total_quantity' => 0,
                        'total_revenue' => 0,
                        'total_cost' => 0,
                        'total_profit' => 0
                    ];
                }
                
                $productProfits[$productId]['total_quantity'] += $item['quantity'];
                $productProfits[$productId]['total_revenue'] += $item['revenue'];
                $productProfits[$productId]['total_cost'] += $item['cost'];
                $productProfits[$productId]['total_profit'] += $item['profit'];
            }
        }
        
        // Calculate profit margins for products
        foreach ($productProfits as &$product) {
            $product['profit_margin'] = $product['total_revenue'] > 0 ? 
                ($product['total_profit'] / $product['total_revenue']) * 100 : 0;
        }
        
        return view('product.labaRugi', [
            'salesResults' => $salesResults,
            'productProfits' => collect($productProfits)->values(),
            'totalRevenue' => $totalRevenue,
            'totalCost' => $totalCost, 
            'totalProfit' => $totalProfit,
            'overallMargin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0,
            'searchProduct' => $searchProduct,
            'searchInvoice' => $searchInvoice
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $gambar = Product::with('productImage')
            ->where('status_active', 1)
            ->get();

        $categories = Categories::all(); // Ambil semua kategori
        $suppliers = Suppliers::all(); // Ambil semua supplier
        $product_image = Product_Image::all(); // Ambil semua gambar produk
        
        // Get all warehouses from the warehouse table
        $warehouses = Warehouse::all(); // Ambil semua warehouse
        
        $products = Product::with('productImage')->get();
        
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
        
        $activeCogs = DB::table('detailkonfigurasi')
            ->where('konfigurasi_id', 7)
            ->where('statusActive', 1)
            ->get();

        // Check for uploaded image in the session
        $uploadedImage = session('uploaded_image');
        
        return view('product.create', compact(
            'gambar',
            'categories', 
            'suppliers', 
            'product_image', 
            'warehouses', 
            'products', 
            'uploadedImage',
            'activeWarehouses',
            'warehouseOptions',
            'activeCogs'
        ));
    }

    /**
     * Store a newly created resource in storage.
     */

     public function store(Request $request)
     {           
         // Validate the request data
         $request->validate([
             'product_name' => 'required|string|max:100',
             'product_desc' => 'required|string',
             'product_price' => 'required|numeric',
             'product_cost' => 'required|numeric',
             'product_stock' => 'required|integer',
             'product_minstock' => 'required|integer|min:1',
             'product_maksretur' => 'required|integer|max:5',
             'product_category_id' => 'required|exists:categories,id',
             'product_supplier_id' => 'required|exists:suppliers,id',
             'cogs_method' => 'required|in:average,fifo',
             'warehouse_option' => 'required|in:multi,direct',
             'product_warehouse_id' => 'required_if:warehouse_option,multi',
             'file_photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
         ]);
     
         // Handle image upload if a file was provided
         $productImageId = null;
         if ($request->hasFile('file_photo')) {
             $file = $request->file('file_photo');
             $folder = 'images';
             $filename = time() . "_" . $file->getClientOriginalName();
             $file->move(public_path($folder), $filename);
             
             // Create new image record
             $productImage = new Product_Image();
             $productImage->name = $filename;
             $productImage->save();
             
             $productImageId = $productImage->id;
         } else {
             // Use selected image if no file was uploaded
             $productImageId = $request->get('product_image_id') ?: null;
         }
     
         // Create new product with all attributes from the table
         $data = new Product();
         $data->name = $request->get('product_name');
         $data->desc = $request->get('product_desc');
         $data->price = $request->get('product_price');
         $data->cost = $request->get('product_cost');
         $data->stock = $request->get('product_stock');
         $data->cogs_methods = $request->get('cogs_method'); // Enum('average','fifo')
         $data->minimum_stock = $request->get('product_minstock');
         $data->maksimum_retur = $request->get('product_maksretur');
         $data->in_order_penjualan = 0; // Initialize to 0
         $data->in_order_pembelian = 0; // Initialize to 0
         $data->status_active = 1; // Set active by default
         $data->categories_id = $request->get('product_category_id');
         $data->product_image_id = $productImageId;
         $data->suppliers_id = $request->get('product_supplier_id');
         // timestamps (created_at, updated_at) will be handled automatically by Laravel
         
         $data->save();
         
         // Handle COGS method - specifically for FIFO
         if ($data->cogs_methods === 'fifo' && $request->get('product_stock') > 0) {
             // Create FIFO record for initial stock
             DB::table('product_fifo')->insert([
                 'product_id' => $data->id,
                 'purchase_date' => now(),
                 'stock' => $request->get('product_stock'),
                 'price' => $data->cost,
                 'purchase_id' => null, // Initial stock, not from a purchase
             ]);
         }
         // For "average" method, stock is already saved in the product table
     
         // Check warehouse option
         $warehouseOption = $request->get('warehouse_option');
          
         // Save to product_has_warehouse only if multi-warehouse is selected
         if ($warehouseOption === 'multi') {
             $warehouseId = $request->get('product_warehouse_id');
             $stock = $request->get('product_stock');
      
             if ($warehouseId) {
                 DB::table('product_has_warehouse')->insert([
                     'product_id' => $data->id,
                     'warehouse_id' => $warehouseId,
                     'stock' => $stock,
                 ]);
             }
         }
          
         return redirect()->route("product.index")->with('status', "Horray, Your new product data is already inserted");
     }


    public function createShipping(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:product,id',
            'quantity_shipped' => 'required|integer|min:1',
        ]);

        $product = Product::find($validatedData['product_id']);
        $productFifoEntries = ProductFifo::where('product_id', $validatedData['product_id'])
            ->where('stock', '>', 0)
            ->orderBy('purchase_date', 'asc')
            ->get();

        $quantityToShip = $validatedData['quantity_shipped'];

        // Check if there are enough items in stock
        if ($product->stock < $quantityToShip) {
            return redirect()->route('sales.shipping')->with('error', 'Insufficient stock.');
        }

        // Reduce stock in FIFO order
        foreach ($productFifoEntries as $fifoEntry) {
            if ($quantityToShip <= 0) break;

            $reduceQuantity = min($fifoEntry->stock, $quantityToShip);
            $fifoEntry->stock -= $reduceQuantity;
            $fifoEntry->save();

            $quantityToShip -= $reduceQuantity;
        }

        // Update main product stock and in_order
        $product->stock -= $validatedData['quantity_shipped'];
        $product->in_order_penjualan -= $validatedData['quantity_shipped'];
        $product->save();

        return redirect()->route('sales.shipping')->with('success', 'Shipment created successfully.');
    }

    public function confirmReceipt(Product $product)
    {
        $productFifoEntries = ProductFifo::where('product_id', $product->id)
            ->where('stock', '>', 0)
            ->orderBy('purchase_date', 'asc')
            ->get();

        $quantityToReduce = $product->in_order_penjualan;

        // Reduce stock in FIFO order
        foreach ($productFifoEntries as $fifoEntry) {
            if ($quantityToReduce <= 0) break;

            $reduceQuantity = min($fifoEntry->stock, $quantityToReduce);
            $fifoEntry->stock -= $reduceQuantity;
            $fifoEntry->save();

            $quantityToReduce -= $reduceQuantity;
        }

        // Reset main product stock and in_order
        $product->stock -= $product->in_order_penjualan;
        $product->in_order_penjualan = 0;
        $product->save();

        return redirect()->route('sales.shipping')->with('success', 'Shipment receipt confirmed and stock updated.');
    }

    public function createShippingPurchase(Request $request)
    {
        $validatedData = $request->validate([
            'product_id' => 'required|exists:product,id',
            'quantity_shipped' => 'required|integer|min:1',
        ]);
    
        $product = Product::find($validatedData['product_id']);
        
        if ($product->in_order_pembelian <= 0) {
            return redirect()->route('purchase.shipping')->with('error', 'No orders pending for this product.');
        }
    
        $product->stock += $validatedData['quantity_shipped'];
        $product->in_order_pembelian -= $validatedData['quantity_shipped'];
        $product->save();
    
        return redirect()->route('purchase.shipping')->with('success', 'Shipment created successfully.');
    }
    
    public function confirmReceiptPurchase(Product $product)
    {
        if ($product->in_order_pembelian > 0) {
            $product->stock += $product->in_order_pembelian;
            $product->in_order_pembelian = 0;
            $product->save();
            
            return redirect()->route('purchase.shipping')->with('success', 'Shipment receipt confirmed and stock updated.');
        } 
        
        return redirect()->route('purchase.shipping')->with('error', 'No stock in order to confirm.');
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
        $data = Product::find($id);
        return view("product.edit", compact('data'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        $updatedData = $product;
        $updatedData->name = $request->name;
        $updatedData->desc = $request->desc;
        $updatedData->price = $request->price;
        $updatedData->cost = $request->cost;
        // $updatedData->stock = $request->stock;
        // $updatedData->cogs_methods = $request->cogs_methods;
        $updatedData->minimum_stock = $request->minimum_stock;
        $updatedData->maksimum_retur = $request->maksimum_retur;
        $updatedData->status_active = $request->has('status_active') ? 1 : 0;
        $updatedData->save();

        return redirect()->route("product.index")->with('status', "Horray, Your customer data is already updated");

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        try {
            //if no contraint error, then delete data. Redirect to index after it.
            $deletedData = $product;
            $deletedData->delete();
            return redirect()->route('product.index')->with('status', 'Horray ! Your data is successfully deleted !');
        } catch (\PDOException $ex) {
            // Failed to delete data, then show exception message
            $msg = "Failed to delete data ! Make sure there is no related data before deleting it";
            return redirect()->route('product.index')->with('status', $msg);
        }
    }
}
