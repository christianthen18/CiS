@extends('layouts.conquer')

@section('content')
    <div class="container">
        <!-- Header Section -->
        <br>
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Sales List</h2>
            <a href="{{ route('sales.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Sales Transactions
            </a>
        </div>

        <!-- Filter Section -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="mb-3">Sales Filter</h5>
                <form action="{{ route('sales.index') }}" method="GET">
                    <div class="row g-3">
                        <!-- Date Range -->
                        <div class="col-md-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                value="{{ request('start_date') }}">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control datepicker" placeholder="dd/mm/yyyy"
                                value="{{ request('end_date') }}">
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

                        <!-- Invoice Input -->
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
                            <a href="{{ route('sales.index') }}" class="btn btn-secondary ms-2">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sales Table -->
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Sales Date</th>
                            <th>Total Price</th>
                            <th>Payment Method</th>
                            <th>Shipped Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($datas as $sale)
                            <tr>
                                <td>{{ $sale->noNota }}</td>
                                <td>{{ $sale->customer->name }}</td>
                                <td>{{ $sale->date }}</td>
                                <td>Rp {{ number_format($sale->total_price, 0, ',', '.') }}</td>
                                <td>{{ $sale->paymentMethod->name }}</td>
                                <td>{{ $sale->shipped_date }}</td>
                                <td>
                                    <a href="{{ route('sales.detail', $sale->id) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
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

@push('scripts')
    <script>
        $(document).ready(function() {
            $(".datepicker").datepicker({
                dateFormat: 'dd/mm/yy',
                changeMonth: true,
                changeYear: true,
                yearRange: '2020:2030',
                showOtherMonths: true,
                selectOtherMonths: true,
                autoclose: true,
                todayHighlight: true
            });
            // Remove readonly attribute to make the fields clickable
            $(".datepicker").prop('readonly', false);
        });
    </script>
@endpush
