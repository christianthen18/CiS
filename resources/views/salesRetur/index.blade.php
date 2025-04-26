@extends('layouts.conquer')

@section('content')
    <div class="page-content">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center mb-4">Return Information</h2>
                
                <!-- Search and Filter Form -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#searchCollapse" aria-expanded="true">
                                <i class="fas fa-search"></i> Search & Filter Returns
                            </button>
                        </h5>
                    </div>
                    <div id="searchCollapse" class="collapse {{ request()->hasAny(['search', 'status', 'date_from', 'date_to']) ? 'show' : '' }}">
                        <div class="card-body">
                            <form action="{{ route('salesRetur.index') }}" method="GET">
                                <div class="row">
                                    <!-- Invoice/Customer Search -->
                                    <div class="col-md-6 mb-3">
                                        <label for="search">Search by invoice or customer</label>
                                        <input type="text" id="search" name="search" class="form-control" placeholder="Enter invoice number or customer name" value="{{ request('search') }}">
                                    </div>
                                    
                                    <!-- Status Filter -->
                                    <div class="col-md-6 mb-3">
                                        <label for="status">Status</label>
                                        <select name="status" id="status" class="form-control">
                                            <option value="">All Statuses</option>
                                            @foreach($statuses as $statusOption)
                                                <option value="{{ $statusOption }}" {{ request('status') == $statusOption ? 'selected' : '' }}>
                                                    {{ $statusOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    
                                    <!-- Date Range -->
                                    <div class="col-md-6 mb-3">
                                        <label for="date_from">From Date</label>
                                        <input type="date" id="date_from" name="date_from" class="form-control" value="{{ request('date_from') }}">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="date_to">To Date</label>
                                        <input type="date" id="date_to" name="date_to" class="form-control" value="{{ request('date_to') }}">
                                    </div>
                                </div>
                                
                                <div class="text-right">
                                    <a href="{{ route('salesRetur.index') }}" class="btn btn-secondary">Reset</a>
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Results Summary (if filters are applied) -->
                @if(request()->hasAny(['search', 'status', 'date_from', 'date_to']))
                    <div class="alert alert-info">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <strong>Search Results:</strong> {{ $returs->count() }} returns found
                                
                                @if(request('search'))
                                    <span class="badge badge-primary ml-2">Search: "{{ request('search') }}"</span>
                                @endif
                                
                                @if(request('status'))
                                    <span class="badge badge-primary ml-2">Status: {{ request('status') }}</span>
                                @endif
                                
                                @if(request('date_from'))
                                    <span class="badge badge-primary ml-2">From: {{ request('date_from') }}</span>
                                @endif
                                
                                @if(request('date_to'))
                                    <span class="badge badge-primary ml-2">To: {{ request('date_to') }}</span>
                                @endif
                            </div>
                            <a href="{{ route('salesRetur.index') }}" class="btn btn-sm btn-outline-secondary">Clear All Filters</a>
                        </div>
                    </div>
                @endif
                
                <div class="d-flex justify-content-end mb-3">
                    <a href="{{ route('salesRetur.create') }}" class="btn btn-info"
                        style="background-color: #040404; color: white; border: none; transition: background-color 0.3s;">
                        + New Return
                    </a>
                </div>
                
                <div class="row">
                    @if($returs->count() > 0)
                        @foreach ($returs as $retur)
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <div class="card-body">
                                        <h3 class="card-title text-center">Sales Return</h3>
                                        <h5 class="card-title text-center">Invoice Number: {{ $retur->invoice_number }}</h5>
                                        <p class="card-text text-center">
                                            <strong>Customer Name:</strong> {{ $retur->customer->name }}
                                        </p>
                                        <p class="card-text text-center">
                                            <strong>Status:</strong> {{ $retur->status }}
                                        </p>
                                        <p class="card-text text-center">
                                            <strong>Date:</strong> {{ $retur->created_at->format('Y-m-d') }}
                                        </p>
                                        <div class="text-center">
                                            <a class="btn btn-warning btn-sm"
                                                style="background-color: rgb(9, 9, 9); color: white; margin-right: 5px;"
                                                href="{{ route('salesRetur.detail', $retur->id) }}">Detail</a>
                                            <form method="POST" action="{{ route('salesRetur.destroy', $retur->id) }}"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="submit" value="Delete" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure to delete return for {{ $retur->invoice_number }}?');">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                <h4><i class="fas fa-exclamation-triangle"></i> No returns found</h4>
                                <p>Try adjusting your search criteria or create a new return</p>
                                <div>
                                    <a href="{{ route('salesRetur.index') }}" class="btn btn-outline-primary mr-2">Show All Returns</a>
                                    <a href="{{ route('salesRetur.create') }}" class="btn btn-primary">Create New Return</a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection