@extends('layouts.conquer')

@section('content')
    <div class="container mt-4">
        <h1>Receiving Management</h1>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger">
                {{ session('error') }}
            </div>
        @endif

        <!-- Search Panel -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#searchCollapse" aria-expanded="true">
                        <i class="fas fa-search"></i> Search & Filter Receives
                    </button>
                </h5>
            </div>
            <div id="searchCollapse" class="collapse {{ request()->hasAny(['search', 'supplier_id', 'warehouse_id', 'date_from', 'date_to', 'status']) ? 'show' : '' }}">
                <div class="card-body">
                    <form action="{{ route('purchase.receiving') }}" method="GET">
                        <div class="row">
                            <!-- Invoice/Supplier Search -->
                            <div class="col-md-6 mb-3">
                                <label for="search">Search</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                    placeholder="Invoice number or supplier name" 
                                    value="{{ request('search') }}">
                            </div>
                            
                            <!-- Supplier Filter -->
                            <div class="col-md-6 mb-3">
                                <label for="supplier_id">Supplier</label>
                                <select name="supplier_id" id="supplier_id" class="form-control">
                                    <option value="">All Suppliers</option>
                                    @foreach($suppliers as $supplier)
                                        <option value="{{ $supplier->id }}" {{ request('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                            {{ $supplier->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Warehouse Filter -->
                            <div class="col-md-6 mb-3">
                                <label for="warehouse_id">Warehouse</label>
                                <select name="warehouse_id" id="warehouse_id" class="form-control">
                                    <option value="">All Warehouses</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ request('warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <!-- Date Range -->
                            <div class="col-md-6 mb-3">
                                <div class="row">
                                    <div class="col-6">
                                        <label for="date_from">From Date</label>
                                        <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                    </div>
                                    <div class="col-6">
                                        <label for="date_to">To Date</label>
                                        <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Status Filter -->
                            <div class="col-md-6 mb-3">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="all" {{ request('status', 'all') == 'all' ? 'selected' : '' }}>All Orders</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending Receives Only</option>
                                    <option value="received" {{ request('status') == 'received' ? 'selected' : '' }}>Received Orders Only</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <a href="{{ route('purchase.receiving') }}" class="btn btn-secondary">Reset</a>
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Search Results Indicator -->
        @if(request()->hasAny(['search', 'supplier_id', 'warehouse_id', 'date_from', 'date_to']))
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Filter Results:</strong>
                        {{ $pendingShipments->count() }} pending receive(s) and {{ $shippedOrders->count() }} received order(s) found
                        
                        @if(request('search'))
                            <span class="badge badge-primary ml-2">Search: "{{ request('search') }}"</span>
                        @endif
                        
                        @if(request('supplier_id'))
                            <span class="badge badge-primary ml-2">Supplier: {{ $suppliers->where('id', request('supplier_id'))->first()->company_name }}</span>
                        @endif
                        
                        @if(request('warehouse_id'))
                            <span class="badge badge-primary ml-2">Warehouse: {{ $warehouses->where('id', request('warehouse_id'))->first()->name }}</span>
                        @endif
                        
                        @if(request('date_from'))
                            <span class="badge badge-primary ml-2">From: {{ request('date_from') }}</span>
                        @endif
                        
                        @if(request('date_to'))
                            <span class="badge badge-primary ml-2">To: {{ request('date_to') }}</span>
                        @endif
                        
                        @if(request('status') && request('status') != 'all')
                            <span class="badge badge-primary ml-2">
                                Status: {{ request('status') == 'pending' ? 'Pending Receives' : 'Received Orders' }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('purchase.receiving') }}" class="btn btn-sm btn-outline-secondary">Clear All Filters</a>
                </div>
            </div>
        @endif

        <!-- Pending Receives Section -->
        @if(request('status', 'all') == 'all' || request('status') == 'pending')
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Product Receive</h5>
                    <span class="badge badge-light">{{ $pendingShipments->count() }} order(s)</span>
                </div>
                <div class="card-body">
                    @if ($pendingShipments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Supplier</th>
                                        <th>Total Price</th>
                                        <th>Payment Method</th>
                                        <th>Warehouse</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingShipments as $purchase)
                                        <tr>
                                            <td>{{ $purchase->noNota }}</td>
                                            <td>{{ date('d M Y', strtotime($purchase->purchase_date)) }}</td>
                                            <td>{{ $purchase->supplier->company_name }}</td>
                                            <td>Rp {{ number_format($purchase->total_price, 2) }}</td>
                                            <td>{{ $purchase->paymentMethod->name }}</td>
                                            <td>{{ $purchase->warehouse ? $purchase->warehouse->name : 'Directly In Store' }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">Pending Product Receive</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('purchase.ship-detail', $purchase->id) }}"
                                                    class="btn btn-primary btn-sm">Process Receive</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No pending receives found.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Received Orders Section -->
        @if(request('status', 'all') == 'all' || request('status') == 'received')
            <div class="card mt-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Received Orders</h5>
                    <span class="badge badge-light">{{ $shippedOrders->count() }} order(s)</span>
                </div>
                <div class="card-body">
                    @if ($shippedOrders->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Supplier</th>
                                        <th>Warehouse</th>
                                        <th>Total Price</th>
                                        <th>Receive Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shippedOrders as $purchase)
                                        <tr>
                                            <td>{{ $purchase->noNota }}</td>
                                            <td>{{ date('d M Y', strtotime($purchase->purchase_date)) }}</td>
                                            <td>{{ $purchase->supplier->company_name }}</td>
                                            <td>{{ $purchase->warehouse ? $purchase->warehouse->name : 'Directly In Store' }}</td>
                                            <td>Rp {{ number_format($purchase->total_price, 2) }}</td>
                                            <td>{{ date('d M Y', strtotime($purchase->receive_date)) }}</td>
                                            <td>
                                                <a href="{{ route('purchase.detail', $purchase->id) }}"
                                                    class="btn btn-info btn-sm">View Details</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(!request()->hasAny(['search', 'supplier_id', 'warehouse_id', 'date_from', 'date_to', 'status']) && $shippedOrders->count() == 10)
                            <div class="text-center mt-3">
                                <p class="text-muted">Showing the 10 most recent received orders. Use the search filters to see more.</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            No received orders found.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endsection