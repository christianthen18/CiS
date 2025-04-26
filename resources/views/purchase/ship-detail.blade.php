@extends('layouts.conquer')

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Process Receiving - {{ $purchase->noNota }}</h5>
                <a href="{{ route('purchase.receiving') }}" class="btn btn-sm btn-light">Back to Purchases</a>
            </div>
            <div class="card-body">
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

                <div class="row mb-4">
                    <div class="col-md-6">
                        <h6>Supplier Information</h6>
                        <p><strong>Name:</strong> {{ $purchase->supplier->company_name ?? 'N/A' }}</p>
                        <p><strong>Address:</strong> {{ $purchase->supplier->address ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $purchase->supplier->phone_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Purchase Information</h6>
                        <p><strong>Invoice:</strong> {{ $purchase->noNota }}</p>
                        <p><strong>Date:</strong> {{ date('d M Y', strtotime($purchase->purchase_date)) }}</p>
                        <p><strong>Total:</strong> Rp {{ number_format($purchase->total_price, 2) }}</p>
                        <p><strong>Payment Method:</strong> {{ $purchase->paymentMethod->name ?? 'N/A' }}</p>
                        @php
                            $cogsMethodName = DB::table('detailkonfigurasi')
                                ->where('id', $purchase->cogs_method_id ?? 0)
                                ->value('name');
                            
                            // Get warehouse information
                            $warehouseInfo = DB::table('warehouse')
                                ->where('id', $purchase->warehouse_id)
                                ->first();
                        @endphp
                        <p><strong>Warehouse:</strong> {{ $warehouseInfo->name ?? 'Directly In Store' }}</p>
                    </div>
                </div>

                <h6 class="mb-3">Purchase Items</h6>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Order Quantity</th>
                                <th>Remaining to Receive</th>
                                <th>Receiving Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $anyItemsToReceive = false;
                            @endphp

                            @foreach ($purchase->purchaseDetails as $detail)
                                @php
                                    // Get the product
                                    $product = App\Models\Product::find($detail->product_id);
                                    if (!$product) {
                                        continue;
                                    }

                                    // Total ordered for this purchase
                                    $totalOrderedForPurchase = $detail->quantity;

                                    // Calculate received quantity for this detail
                                    $receivedForThisDetail = DB::table('receive_history')
                                        ->where('product_id', $detail->product_id)
                                        ->where('purchase_id', $detail->purchase_id)
                                        ->sum('quantity_received') ?? 0;

                                    // Calculate remaining to receive
                                    $remainingToReceive = $totalOrderedForPurchase - $receivedForThisDetail;

                                    // Track if any items are left to receive
                                    if ($remainingToReceive > 0) {
                                        $anyItemsToReceive = true;
                                    }
                                @endphp

                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>Rp {{ number_format($detail->subtotal_price / $detail->quantity, 2) }}</td>
                                    <td>{{ $totalOrderedForPurchase }}</td>
                                    <td class="remaining-cell">{{ $remainingToReceive }}</td>
                                    <td>
                                        <form action="{{ route('purchase.create-receiving', $product->id) }}" method="POST"
                                            class="receiving-form">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                                            <!-- Gunakan product_id dan purchase_id dari detail sebagai identifier -->
                                            <input type="hidden" name="detail_product_id"
                                                value="{{ $detail->product_id }}">
                                            <input type="hidden" name="detail_purchase_id" value="{{ $detail->purchase_id }}">
                                            <!-- Use warehouse_id from purchase table -->
                                            <input type="hidden" name="warehouse_id" value="{{ $purchase->warehouse_id }}">

                                            <input type="number" name="quantity_received" class="form-control"
                                                min="1" max="{{ $remainingToReceive }}"
                                                value="{{ $remainingToReceive > 0 ? $remainingToReceive : 0 }}"
                                                {{ $remainingToReceive <= 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-primary receive-btn"
                                            {{ $remainingToReceive <= 0 ? 'disabled' : '' }}>
                                            Receive
                                        </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <form action="{{ route('purchase.receive-all', $purchase->id) }}" method="POST">
                        @csrf
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label for="received_date">Receiving Date</label>
                                <input type="date" name="received_date" id="received_date" class="form-control"
                                    value="{{ date('Y-m-d') }}" required>
                            </div>
                            {{-- <div class="col-md-8">
                                <button type="submit" class="btn btn-success" id="receive-all-btn"
                                    {{ !$anyItemsToReceive ? 'disabled' : '' }}>
                                    Receive All Available
                                </button>
                            </div> --}}
                        </div>
                    </form>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info">
                        <strong>Note:</strong> Stock addition method is based on the selected COGS method:
                        <ul>
                            <li><strong>FIFO:</strong> Stock will be added to both the product table and the
                                product_fifo table with current purchase information.</li>
                            <li><strong>Average:</strong> Stock will only be added to the product table.</li>
                        </ul>
                        <p><strong>Warehouse:</strong> Stock will be added to the warehouse specified in the purchase record ({{ $warehouseInfo->name ?? 'Default Warehouse' }}).</p>
                        <p class="mb-0"><strong>Note:</strong> Receiving quantity cannot exceed the order quantity for this
                            specific purchase. The "Remaining to Receive" column shows the quantity that can still be received
                            for this purchase.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validation to prevent receiving more than available
            const receivingForms = document.querySelectorAll('.receiving-form');

            receivingForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const quantityInput = this.querySelector('input[name="quantity_received"]');
                    const quantity = parseInt(quantityInput.value);
                    const max = parseInt(quantityInput.getAttribute('max'));

                    if (quantity <= 0) {
                        e.preventDefault();
                        alert('Receiving quantity must be greater than zero.');
                    } else if (quantity > max) {
                        e.preventDefault();
                        alert('Cannot receive more than available to receive for this purchase.');
                        quantityInput.value = max;
                    }
                });
            });

            // Function to check if any items remain to receive and update "Receive All" button
            function updateReceiveAllButton() {
                const remainingCells = document.querySelectorAll('.remaining-cell');
                let anyItemsToReceive = false;

                remainingCells.forEach(cell => {
                    if (parseInt(cell.textContent) > 0) {
                        anyItemsToReceive = true;
                    }
                });

                const receiveAllBtn = document.getElementById('receive-all-btn');
                if (receiveAllBtn) {
                    receiveAllBtn.disabled = !anyItemsToReceive;
                }
            }

            // Initial check
            updateReceiveAllButton();
        });
    </script>
@endsection