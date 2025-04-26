@extends('layouts.conquer')

@section('content')
    <div class="container mt-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

         <!-- Header Section -->
         <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Purchase List</h2>
            <a href="{{ route('purchase.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Purchase
            </a>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Purchase Filter</h5>
                <form action="{{ route('purchase.index') }}" method="GET">
                    <div class="row g-3">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control"
                                value="{{ request('start_date') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                        </div>

                        <!-- Product Dropdown -->
                        <div class="col-md-3">
                            <label class="form-label">Products</label>
                            <select name="product_id" class="form-select">
                                <option value="">All Product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}"
                                        {{ request('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Invoice Dropdown -->
                        <div class="col-md-3">
                            <label class="form-label">Invoice</label>
                            <select name="invoice" class="form-select">
                                <option value="">All Invoice</option>
                                @foreach ($invoices as $invoice)
                                    <option value="{{ $invoice->noNota }}"
                                        {{ request('invoice') == $invoice->noNota ? 'selected' : '' }}>
                                        {{ $invoice->noNota }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Buttons -->
                        <div class="col-12 mt-3">
                            <button type="submit" class="btn btn-primary">Filter</button>
                            <a href="{{ route('purchase.index') }}" class="btn btn-secondary ms-2">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

       

        <!-- Purchase List Table -->
        <div class="card shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Invoice</th>
                                <th>Supplier</th>
                                <th>Purchase Date</th>
                                <th>Total Price</th>
                                <th>Payment Method</th>
                                <th>Warehouse</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($datas as $purchase)
                                <tr>
                                    <td>{{ $purchase->noNota }}</td>
                                    <td>{{ $purchase->supplier->company_name }}</td>
                                    <td>{{ $purchase->purchase_date }}</td>
                                    <td>Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</td>
                                    <td>{{ $purchase->paymentMethod->name }}</td>
                                    <td>{{ $purchase->warehouse ? $purchase->warehouse->name : 'Directly in store' }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('purchase.show', $purchase->id) }}"
                                                class="btn btn-info btn-sm" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center">No purchase records found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .card {
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .form-control,
        .form-select {
            border-radius: 4px;
            border: 1px solid #ced4da;
            padding: 8px 12px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .btn {
            padding: 8px 16px;
            border-radius: 4px;
        }
    </style>
@endpush
