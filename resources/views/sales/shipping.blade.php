@extends('layouts.conquer')

@section('content')
    <div class="container mt-4">
        <h1>Shipping Management</h1>

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
                        <i class="fas fa-search"></i> Search & Filter Shipments
                    </button>
                </h5>
            </div>
            <div id="searchCollapse" class="collapse {{ request()->hasAny(['search', 'customer_id', 'date_from', 'date_to', 'shipping_type']) ? 'show' : '' }}">
                <div class="card-body">
                    <form action="{{ route('sales.shipping') }}" method="GET">
                        <div class="row">
                            <!-- Invoice/Customer Search -->
                            <div class="col-md-6 mb-3">
                                <label for="search">Search</label>
                                <input type="text" id="search" name="search" class="form-control" 
                                    placeholder="Invoice number, customer name, recipient, address..." 
                                    value="{{ request('search') }}">
                            </div>
                            
                            <!-- Customer Filter -->
                            <div class="col-md-6 mb-3">
                                <label for="customer_id">Customer</label>
                                <select name="customer_id" id="customer_id" class="form-control">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }}
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
                            
                            <!-- Shipment Type Filter -->
                            <div class="col-md-6 mb-3">
                                <label for="shipping_type">Shipment Status</label>
                                <select name="shipping_type" id="shipping_type" class="form-control">
                                    <option value="all" {{ request('shipping_type', 'all') == 'all' ? 'selected' : '' }}>All Shipments</option>
                                    <option value="pending" {{ request('shipping_type') == 'pending' ? 'selected' : '' }}>Pending Shipments Only</option>
                                    <option value="shipped" {{ request('shipping_type') == 'shipped' ? 'selected' : '' }}>Shipped Orders Only</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="text-right">
                            <a href="{{ route('sales.shipping') }}" class="btn btn-secondary">Reset</a>
                            <button type="submit" class="btn btn-primary">Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Search Results Indicator -->
        @if(request()->hasAny(['search', 'customer_id', 'date_from', 'date_to']))
            <div class="alert alert-info mb-4">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong>Filter Results:</strong>
                        {{ $pendingShipments->count() }} pending shipment(s) and {{ $shippedOrders->count() }} shipped order(s) found
                        
                        @if(request('search'))
                            <span class="badge badge-primary ml-2">Search: "{{ request('search') }}"</span>
                        @endif
                        
                        @if(request('customer_id'))
                            <span class="badge badge-primary ml-2">Customer: {{ $customers->where('id', request('customer_id'))->first()->name }}</span>
                        @endif
                        
                        @if(request('date_from'))
                            <span class="badge badge-primary ml-2">From: {{ request('date_from') }}</span>
                        @endif
                        
                        @if(request('date_to'))
                            <span class="badge badge-primary ml-2">To: {{ request('date_to') }}</span>
                        @endif
                        
                        @if(request('shipping_type') && request('shipping_type') != 'all')
                            <span class="badge badge-primary ml-2">
                                Status: {{ request('shipping_type') == 'pending' ? 'Pending Shipments' : 'Shipped Orders' }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('sales.shipping') }}" class="btn btn-sm btn-outline-secondary">Clear All Filters</a>
                </div>
            </div>
        @endif

        <!-- Pending Shipments Section -->
        @if(request('shipping_type', 'all') == 'all' || request('shipping_type') == 'pending')
            <div class="card">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Pending Shipments</h5>
                    <span class="badge badge-light">{{ $pendingShipments->count() }} shipment(s)</span>
                </div>
                <div class="card-body">
                    @if ($pendingShipments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Invoice</th>
                                        <th>Date</th>
                                        <th>Customer</th>
                                        <th>Total Price</th>
                                        <th>Payment Method</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pendingShipments as $sale)
                                        <tr>
                                            <td>{{ $sale->noNota }}</td>
                                            <td>{{ date('d M Y', strtotime($sale->date)) }}</td>
                                            <td>{{ $sale->customer->name }}</td>
                                            <td>Rp {{ number_format($sale->total_price, 2) }}</td>
                                            <td>{{ $sale->paymentMethod->name }}</td>
                                            <td>
                                                <span class="badge bg-warning text-dark">Pending Shipment</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('sales.ship-detail', $sale->id) }}"
                                                    class="btn btn-primary btn-sm">Process Shipment</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            No pending shipments found.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Shipped Orders Section -->
        @if(request('shipping_type', 'all') == 'all' || request('shipping_type') == 'shipped')
            <div class="card mt-4">
                <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Shipped Orders</h5>
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
                                        <th>Customer</th>
                                        <th>Recipient</th>
                                        <th>Shipping Address</th>
                                        <th>Total Price</th>
                                        <th>Latest Shipment</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($shippedOrders as $sale)
                                        <tr>
                                            <td>{{ $sale->noNota }}</td>
                                            <td>{{ date('d M Y', strtotime($sale->date)) }}</td>
                                            <td>{{ $sale->customer->name }}</td>
                                            <td>{{ $sale->recipients_name ?? $sale->customer->name }}</td>
                                            <td>
                                                @if ($sale->shipping_address)
                                                    <span class="text-truncate d-inline-block" style="max-width: 150px;"
                                                        title="{{ $sale->shipping_address }}">
                                                        {{ $sale->shipping_address }}
                                                    </span>
                                                @else
                                                    <span class="text-muted">Default Address</span>
                                                @endif
                                            </td>
                                            <td>Rp {{ number_format($sale->total_price, 2) }}</td>
                                            <td>
                                                @if (isset($sale->latest_shipped_at))
                                                    {{ date('d M Y H:i', strtotime($sale->latest_shipped_at)) }}
                                                @else
                                                    {{ date('d M Y', strtotime($sale->shipped_date)) }}
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('sales.detail', $sale->id) }}" class="btn btn-info btn-sm">View
                                                    Details</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        @if(!request()->hasAny(['search', 'customer_id', 'date_from', 'date_to', 'shipping_type']) && $shippedOrders->count() == 10)
                            <div class="text-center mt-3">
                                <p class="text-muted">Showing the 10 most recent shipped orders. Use the search filters to see more.</p>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-info">
                            No shipped orders found.
                        </div>
                    @endif
                </div>
            </div>
        @endif
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Add table sorting functionality here if needed
        });
    </script>
@endsection