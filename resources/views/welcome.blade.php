@extends('layouts/conquer')
@section('content')
<div class="container-fluid">
    {{-- @if(Auth::check() && Auth::user()->status_active == 0)
    <div class="alert alert-danger text-center mb-4">
        <h4><i class="fas fa-exclamation-circle"></i> Account Inactive</h4>
        <p>Your account is currently inactive. You have limited access to the system.</p>
        <p>Please contact an administrator for assistance.</p>
    </div>
    @endif --}}

    <div class="dashboard-content {{ Auth::check() && Auth::user()->status_active == 0 ? 'disabled-content' : '' }}">
        <div class="row">
            <!-- Sales Summary Cards -->
            <div class="col-md-3">
                <div class="card card-stats card-primary">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                            </div>
                            <div class="col-7 d-flex align-items-center">
                                <div class="numbers">
                                    <p class="card-category">Total Sales</p>
                                    <h4 class="card-title">Rp {{ number_format($totalSales ?? 0, 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stats card-success">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-shopping-cart"></i>
                                </div>
                            </div>
                            <div class="col-7 d-flex align-items-center">
                                <div class="numbers">
                                    <p class="card-category">Orders This Month</p>
                                    <h4 class="card-title">{{ $ordersThisMonth ?? 0 }} units</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stats card-warning">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5"> 
                                <div class="icon-big text-center">
                                    <i class="fas fa-box"></i>
                                </div>
                            </div>
                            <div class="col-7 d-flex align-items-center">
                                <div class="numbers">
                                    <p class="card-category">Low Stock Products</p>
                                    <h4 class="card-title">{{ $lowStockProducts ?? 0 }}</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card card-stats card-danger">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-undo"></i>
                                </div>
                            </div>
                            <div class="col-7 d-flex align-items-center">
                                <div class="numbers">
                                    <p class="card-category">Sales Returns</p>
                                    <h4 class="card-title">{{ $returPenjualan ?? 0 }} units</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-3">
            <div class="col-md-3">
                <div class="card card-stats card-warning">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-5">
                                <div class="icon-big text-center">
                                    <i class="fas fa-exchange-alt"></i>
                                </div>
                            </div>
                            <div class="col-7 d-flex align-items-center">
                                <div class="numbers">
                                    <p class="card-category">Purchase Returns</p>
                                    <h4 class="card-title">{{ $returPembelian ?? 0 }} units</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Action Buttons -->
        <div class="row mt-3">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Quick Actions</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('sales.create') }}" class="btn btn-primary btn-block {{ Auth::check() && Auth::user()->status_active == 0 ? 'disabled' : '' }}">
                                    <i class="fas fa-plus"></i> Create New Sale
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('purchase.create') }}" class="btn btn-success btn-block {{ Auth::check() && Auth::user()->status_active == 0 ? 'disabled' : '' }}">
                                    <i class="fas fa-shopping-bag"></i> Create Purchase
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('product.index') }}" class="btn btn-info btn-block {{ Auth::check() && Auth::user()->status_active == 0 ? 'disabled' : '' }}">
                                    <i class="fas fa-boxes"></i> Manage Products
                                </a>
                            </div>
                            <div class="col-md-3 mb-2">
                                <a href="{{ route('salesRetur.index') }}" class="btn btn-warning btn-block {{ Auth::check() && Auth::user()->status_active == 0 ? 'disabled' : '' }}">
                                    <i class="fas fa-undo"></i> Manage Sales Returns
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Sales and Purchases -->
        <div class="row mt-3">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recent Sales</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Customer</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recentSales))
                                    @foreach ($recentSales as $sale)
                                        <tr>
                                            <td>{{ $sale->noNota }}</td>
                                            <td>{{ $sale->customer->name }}</td>
                                            <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                                            <td>{{ $sale->date }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Recent Purchases</h4>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Supplier</th>
                                    <th>Total</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(isset($recentPurchases))
                                    @foreach ($recentPurchases as $purchase)
                                        <tr>
                                            <td>{{ $purchase->noNota }}</td>
                                            <td>{{ $purchase->supplier->company_name }}</td>
                                            <td>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                                            <td>{{ $purchase->purchase_date }}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sales and Purchase Chart -->
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Monthly Sales and Purchase Overview</h4>
                    </div>
                    <div class="card-body">
                        <canvas id="salesPurchaseChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .disabled-content {
        position: relative;
        pointer-events: none;
        opacity: 0.7;
    }
    
    .disabled-content::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.05);
        z-index: 100;
    }
</style>

@if(Auth::check() && Auth::user()->status_active == 0)
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Disable all links in the dashboard
        const links = document.querySelectorAll('.dashboard-content a');
        links.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Account Inactive',
                    text: 'Your account is currently inactive. Please contact an administrator for assistance.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            });
        });
        
        // Disable all buttons in the dashboard
        const buttons = document.querySelectorAll('.dashboard-content button');
        buttons.forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    icon: 'error',
                    title: 'Account Inactive',
                    text: 'Your account is currently inactive. Please contact an administrator for assistance.',
                    confirmButtonColor: '#d33',
                    confirmButtonText: 'OK'
                });
            });
        });
    });
</script>
@endif
@endsection