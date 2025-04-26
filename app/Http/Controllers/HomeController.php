<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\purchase;
use App\Models\Retur;
use App\Models\Sales;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Prepare dashboard statistics
        $totalSales = Sales::sum('total_price');
        $ordersThisMonth = Sales::whereMonth('date', now()->month)->count();
        $lowStockProducts = Product::where('stock', '<=', 'minimum_stock')->count();
    
        // Count returns by type
        $returPenjualan = Retur::where('type', 'penjualan')->count();
        $returPembelian = Retur::where('type', 'pembelian')->count();
    
        // Recent sales and purchases (last 5)
        $recentSales = Sales::latest('id')->take(5)->get(); // Change to latest by ID
        $recentPurchases = Purchase::latest('id')->take(5)->get(); // Change to latest by ID
    
        // Prepare chart data for monthly sales and purchases
        $monthlySalesData = Sales::selectRaw('MONTH(date) as month, SUM(total_price) as total_sales')
            ->groupBy('month')
            ->get();
    
        $monthlyPurchasesData = Purchase::selectRaw('MONTH(purchase_date) as month, SUM(total_price) as total_purchases')
            ->groupBy('month')
            ->get();
    
        return view('welcome', [
            'totalSales' => $totalSales, 
            'ordersThisMonth' => $ordersThisMonth, 
            'lowStockProducts' => $lowStockProducts, 
            'returPenjualan' => $returPenjualan,
            'returPembelian' => $returPembelian,
            'recentSales' => $recentSales, 
            'recentPurchases' => $recentPurchases,
            'monthlySalesData' => $monthlySalesData,
            'monthlyPurchasesData' => $monthlyPurchasesData
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
