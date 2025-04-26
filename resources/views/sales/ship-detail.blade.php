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
                <h5 class="mb-0">Process Shipment - {{ $sale->noNota }}</h5>
                <a href="{{ route('sales.shipping') }}" class="btn btn-sm btn-light">Back to Shipments</a>
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
                        <h6>Customer Information</h6>
                        <p><strong>Name:</strong> {{ $sale->customer->name }}</p>
                        <p><strong>Address:</strong> {{ $sale->customer->address ?? 'N/A' }}</p>
                        <p><strong>Phone:</strong> {{ $sale->customer->phone_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Order Information</h6>
                        <p><strong>Invoice:</strong> {{ $sale->noNota }}</p>
                        <p><strong>Date:</strong> {{ date('d M Y', strtotime($sale->date)) }}</p>
                        <p><strong>Total:</strong> Rp {{ number_format($sale->total_price, 2) }}</p>
                        <p><strong>Payment Method:</strong> {{ $sale->paymentMethod->name }}</p>
                    </div>
                </div>

                <!-- Move Shipping Details section outside the product loop -->
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">Shipping Details</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="global_shipping_address">Shipping Address (Optional)</label>
                                            <textarea id="global_shipping_address" class="form-control global-shipping-field" rows="3"
                                                placeholder="Leave empty to use customer's address: {{ $sale->customer->address ?? 'N/A' }}"></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="global_recipients_name">Recipient's Name (Optional)</label>
                                            <input type="text" id="global_recipients_name" class="form-control global-shipping-field"
                                                placeholder="Leave empty to use customer's name: {{ $sale->customer->name ?? 'N/A' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <h6 class="mb-3">Order Items</h6>

                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Order Quantity</th>
                                <th>Available Stock</th>
                                <th>Remaining to Ship</th>
                                <th>Shipping Quantity</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $anyItemsToShip = false;
                            @endphp

                            @foreach ($sale->salesDetail as $detail)
                                @php
                                    // Get the product
                                    $product = App\Models\Product::find($detail->product_id);
                                    if (!$product) {
                                        continue;
                                    }

                                    // Total ordered for this invoice item
                                    $totalOrderedForThisDetail = $detail->total_quantity;

                                    // Calculate shipped quantity for this detail - FIXED: Use composite key
                                    $shippedForThisDetail =
                                        DB::table('shipping_history')
                                            ->where('product_id', $detail->product_id)
                                            ->where('sales_id', $detail->sales_id)
                                            ->sum('quantity_shipped') ?? 0;

                                    // Calculate remaining to ship for this detail
                                    $remainingToShipForDetail = $totalOrderedForThisDetail - $shippedForThisDetail;

                                    // Make sure in_order validation is still enforced
                                    $totalInOrder = $product->in_order_penjualan ?? 0;

                                    // Remaining to ship is the minimum of what's left in this detail and what's in order
                                    $remainingToShip = min($remainingToShipForDetail, $totalInOrder);

                                    // Track if any items are left to ship
                                    if ($remainingToShip > 0) {
                                        $anyItemsToShip = true;
                                    }

                                    // Calculate max shippable quantity
                                    $maxShippable = min($remainingToShip, $product->stock);
                                @endphp

                                <tr>
                                    <td>{{ $product->name }}</td>
                                    <td>Rp {{ number_format($detail->total_price / $detail->total_quantity, 2) }}</td>
                                    <td>{{ $totalOrderedForThisDetail }}</td>
                                    <td>{{ $product->stock }}</td>
                                    <td class="remaining-cell">{{ $remainingToShip }}</td>
                                    <td>
                                        <form action="{{ route('products.create-shipping', $product->id) }}" method="POST"
                                            class="shipping-form">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <input type="hidden" name="sale_id" value="{{ $sale->id }}">
                                            <input type="hidden" name="detail_product_id" value="{{ $detail->product_id }}">
                                            <input type="hidden" name="detail_sales_id" value="{{ $detail->sales_id }}">
                                            <!-- Hidden fields that will be filled from global shipping details -->
                                            <input type="hidden" name="shipping_address" class="shipping-address-field">
                                            <input type="hidden" name="recipients_name" class="recipients-name-field">

                                            <input type="number" name="quantity_shipped" class="form-control"
                                                min="1" max="{{ $maxShippable }}"
                                                value="{{ $maxShippable > 0 ? $maxShippable : 0 }}"
                                                {{ $maxShippable <= 0 ? 'disabled' : '' }}>
                                    </td>
                                    <td>
                                        <button type="submit" class="btn btn-primary ship-btn"
                                            {{ $maxShippable <= 0 ? 'disabled' : '' }}>
                                            Ship
                                        </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">
                    <div class="alert alert-info">
                        <strong>Note:</strong> Stock reduction method is based on the selected COGS method:
                        <ul>
                            <li><strong>FIFO:</strong> Stock will be reduced from both the product table and the
                                product_fifo table in first-in-first-out order.</li>
                            <li><strong>Average:</strong> Stock will only be reduced from the product table.</li>
                        </ul>
                        <p class="mb-0"><strong>Note:</strong> Shipping quantity cannot exceed the order quantity for this
                            specific invoice. The "Remaining to Ship" column shows the quantity that can still be shipped
                            for this invoice.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('javascript')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validation to prevent shipping more than available
            const shippingForms = document.querySelectorAll('.shipping-form');
            const globalShippingAddressField = document.getElementById('global_shipping_address');
            const globalRecipientsNameField = document.getElementById('global_recipients_name');

            // Copy shipping details from global fields to individual form fields on submit
            shippingForms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const quantityInput = this.querySelector('input[name="quantity_shipped"]');
                    const quantity = parseInt(quantityInput.value);
                    const max = parseInt(quantityInput.getAttribute('max'));

                    if (quantity <= 0) {
                        e.preventDefault();
                        alert('Shipping quantity must be greater than zero.');
                        return;
                    } else if (quantity > max) {
                        e.preventDefault();
                        alert('Cannot ship more than available to ship for this invoice.');
                        quantityInput.value = max;
                        return;
                    }

                    // Copy shipping details from global fields to this specific form
                    this.querySelector('.shipping-address-field').value = globalShippingAddressField.value;
                    this.querySelector('.recipients-name-field').value = globalRecipientsNameField.value;
                });
            });

            // Function to check if any items remain to ship and update "Ship All" button
            function updateShipAllButton() {
                const remainingCells = document.querySelectorAll('.remaining-cell');
                let anyItemsToShip = false;

                remainingCells.forEach(cell => {
                    if (parseInt(cell.textContent) > 0) {
                        anyItemsToShip = true;
                    }
                });

                const shipAllBtn = document.getElementById('ship-all-btn');
                if (shipAllBtn) {
                    shipAllBtn.disabled = !anyItemsToShip;
                }
            }

            // Initial check
            updateShipAllButton();
        });
    </script>
@endsection