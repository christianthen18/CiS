@extends('layouts.conquer')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-4">Create Purchase</h2>

        <form method="POST" action="{{ route('purchase.store') }}" id="purchaseForm">
            @csrf
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Invoice Section -->
                    <div class="form-group">
                        <label for="no_nota">Invoice</label>
                        <input type="text" class="form-control" name="no_nota"
                            value="PUR{{ str_pad($newNumber, 4, '0', STR_PAD_LEFT) }}" readonly>
                        <small class="form-text text-muted">This is your invoice number.</small>
                    </div>

                    <!-- Supplier Selection -->
                    <div class="form-group">
                        <label for="supplier_id">Supplier Name</label>
                        <select class="form-control" name="supplier_id" required>
                            <option value="">Select Supplier</option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Please select a supplier.</small>
                    </div>

                    <!-- Date Section -->
                    <div class="form-group">
                        <label for="purchase_date">Purchase Date</label>
                        <input type="date" class="form-control" name="purchase_date" required>
                        <small class="form-text text-muted">Please select the date of the purchase.</small>
                    </div>

                    <!-- Dynamic Warehouse Configuration -->
                    @if (isset($warehouseOptions) && $warehouseOptions->count() > 0)
                        <div class="form-group">
                            <label for="warehouse_selection">Select Warehouse Option:</label><br>

                            @php
                                $multiWarehouseOption = $warehouseOptions->where('id', 14)->first();
                                $directlyInStoreOption = $warehouseOptions->where('id', 15)->first();
                            @endphp

                            @if ($multiWarehouseOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="warehouse_option"
                                        id="multiWarehouse" value="multi" {{ $multiWarehouseOption ? 'checked' : '' }}>
                                    <label class="form-check-label" for="multiWarehouse">
                                        Multi-warehouse
                                    </label>
                                    <div>
                                        <small class="text-muted">{{ $multiWarehouseOption->desc }}</small>
                                    </div>
                                </div>
                            @endif

                            @if ($directlyInStoreOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="warehouse_option"
                                        id="directlyInStore" value="direct"
                                        {{ !$multiWarehouseOption && $directlyInStoreOption ? 'checked' : '' }}>
                                    <label class="form-check-label" for="directlyInStore">
                                        Directly in store
                                    </label>
                                    <div>
                                        <small class="text-muted">{{ $directlyInStoreOption->desc }}</small>
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($multiWarehouseOption)
                            <div id="warehouseDropdown" class="form-group mt-3"
                                {{ !$multiWarehouseOption ? 'style="display:none;"' : '' }}>
                                <label for="warehouse_id">Select Warehouse:</label>
                                <select class="form-control" name="warehouse_id" id="warehouse_id">
                                    <option value="" disabled selected>Select Your Warehouse</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                <small id="warehouseHelp" class="form-text text-muted">Please select a warehouse if
                                    Multi-warehouse is selected.</small>
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            No warehouse options are currently active. Please contact the administrator.
                        </div>
                    @endif

                    <!-- Product Selection -->
                    <h5 class="mt-4">Product Selection</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="product_id">Product Name</label>
                            <select class="form-control" name="product_id" id="product_id" onchange="getPrice(this)">
                                <option value="">Select a product</option>
                                @foreach ($products as $product)
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="price">Price</label>
                            <input type="number" class="form-control" name="price" id="price">
                        </div>
                        <div class="col-md-2">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" value="1"
                                min="1" onchange="if(parseInt(this.value) <= 0) this.value=1;"
                                oninput="this.value = Math.max(1, Math.abs(parseInt(this.value)) || 1)">
                        </div>
                        <div class="col-md-2 align-self-end">
                            <button type="button" class="btn btn-primary w-100" id="addProduct">Add</button>
                        </div>
                    </div>
                    <input type="hidden" name="products" id="productsInput">

                    <!-- List of Products -->
                    <h5 class="mt-4">List of Products</h5>
                    <table class="table table-striped" id="productTable">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Unit Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                    <!-- Total Price Display -->
                    <div class="mt-3">
                        <h5>Total Price: <span id="totalPrice">Rp 0.00</span></h5>
                    </div>

                    <!-- Shipping Section -->
                    <div class="card mt-4 ">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#shippingCollapse">
                            <h5 class="mb-0">Shipping Options <i class="bi bi-chevron-down"></i></h5>
                        </div>
                        <div class="collapse" id="shippingCollapse">
                            <div class="card-body">
                                <label for="receive_date">Shipped Date</label>
                                <input type="date" class="form-control" name="receive_date" id="receive_date">
                            </div>

                            @if ($activeShippings->isNotEmpty())
                                @foreach ($activeShippings as $shipping)
                                    <div class="form-check mb-3">
                                        <input class="form-check-input shipping-radio" type="radio" name="shipping_id"
                                            value="{{ $shipping->value }}" id="shipping{{ $shipping->id }}"
                                            data-name="{{ $shipping->name }}">
                                        <label class="form-check-label" for="shipping{{ $shipping->id }}">
                                            {{ $shipping->name }}
                                        </label>
                                        <div class="ms-4 mt-2 shipping-value-input" style="display: none;">
                                            <div class="input-group" style="max-width: 200px;">
                                                <span class="input-group-text">Rp</span>
                                                <input type="number" class="form-control"
                                                    value="{{ $shipping->value }}" min="0" step="1000">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                <input type="hidden" name="shipping_cost" id="shipping_cost" value="0">
                            @else
                                <p>No active shipping options available.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Final Price Section -->
                    <h5 class="mt-4">Final Price: <span id="finalPrice">Rp 0.00</span></h5>
                    <input type="hidden" name="final_price" id="final_price">

                    <!-- Payment Method Section -->
                    @if ($activePayments->isNotEmpty())
                        <div class="form-group">
                            <label for="payment_methods_id">Payment Method</label>
                            <select class="form-control" name="payment_methods_id" required>
                                <option value="">Select Payment Method</option>
                                @foreach ($activePayments as $paymentMethod)
                                    <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                                @endforeach
                            </select>
                            <small id="paymentMethodHelp" class="form-text text-muted">Please select a payment
                                method</small>
                        </div>
                    @else
                        <p>No active Payment available.</p>
                    @endif

                    <!-- COGS Method Section -->
                    <div class="form-group">
                        <label for="cogs_method">Select Cogs Method:</label>
                        <select class="form-control" name="cogs_method" required>
                            <option value="">Select COGS Method</option>
                            @foreach ($activeCogs as $cogs_method)
                                <option value="{{ strtolower(str_replace('P-', '', $cogs_method->name)) }}">
                                    {{ $cogs_method->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a class="btn btn-info me-2" href="{{ url()->previous() }}">Cancel</a>
                        <button type="button" class="btn btn-primary" onclick="showConfirmation()">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Custom Modal Dialog -->
    <div id="customModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; overflow: auto;">
        <div
            style="background-color: white; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0;">Konfirmasi</h4>
                <span onclick="hideConfirmation()"
                    style="cursor: pointer; font-size: 20px; font-weight: bold;">&times;</span>
            </div>
            <div id="modalContent" style="margin-bottom: 20px;">
                Are you sure you want to make this purchase?
            </div>
            <div style="text-align: right;">
                <button onclick="hideConfirmation()"
                    style="padding: 5px 10px; margin-right: 10px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;">Batal</button>
                <button onclick="submitForm()"
                    style="padding: 5px 10px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 3px; cursor: pointer;">Ya,
                    Lanjutkan</button>
            </div>
        </div>
    </div>

@endsection

@section('javascript')
    <script>
        let totalPrice = 0; // Initialize total price

        function getPrice(selectElement) {
            const priceInput = document.getElementById('price');
            const quantityInput = document.getElementById('quantity');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            priceInput.value = price;

            // Ensure quantity is always at least 1
            if (parseInt(quantityInput.value) <= 0) {
                quantityInput.value = 1;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const products = []; // Initialize an array to hold products
            const productsInput = document.getElementById('productsInput');
            const multiWarehouseRadio = document.getElementById('multiWarehouse');
            const directlyInStoreRadio = document.getElementById('directlyInStore');
            const warehouseDropdown = document.getElementById('warehouseDropdown');
            const warehouseSelect = document.getElementById('warehouse_id');

            if (multiWarehouseRadio && directlyInStoreRadio && warehouseDropdown) {
                // Show/hide warehouse dropdown based on selected option  
                multiWarehouseRadio.addEventListener('change', function() {
                    warehouseDropdown.style.display = 'block';
                    if (warehouseSelect) {
                        warehouseSelect.setAttribute('required', 'required');
                    }
                });

                directlyInStoreRadio.addEventListener('change', function() {
                    warehouseDropdown.style.display = 'none';
                    if (warehouseSelect) {
                        warehouseSelect.removeAttribute('required');
                    }
                });

                // Initialize dropdown visibility  
                warehouseDropdown.style.display = multiWarehouseRadio.checked ? 'block' : 'none';
                if (multiWarehouseRadio.checked && warehouseSelect) {
                    warehouseSelect.setAttribute('required', 'required');
                }
            }

            function addProduct() {
                const productSelect = document.getElementById('product_id');
                const productId = productSelect.value;
                const productName = productSelect.options[productSelect.selectedIndex].text;
                const price = parseFloat(document.getElementById('price').value) || 0;
                const quantity = parseInt(document.getElementById('quantity').value) || 1;

                // Validate quantity is positive
                if (quantity <= 0) {
                    alert("Quantity must be greater than zero");
                    document.getElementById('quantity').value = 1;
                    return;
                }

                const subtotal = price * quantity;

                if (productId) {
                    // Validate warehouse selection if multi-warehouse is selected
                    if (multiWarehouseRadio && multiWarehouseRadio.checked && warehouseSelect && !warehouseSelect
                        .value) {
                        alert('Please select a warehouse first!');
                        return;
                    }

                    // Check if product already exists in the table
                    const existingProductIndex = products.findIndex(p => p.product_id === productId);

                    if (existingProductIndex !== -1) {
                        // Product already exists, update quantity instead of adding a new row
                        const existingProduct = products[existingProductIndex];
                        const newQuantity = existingProduct.quantity + quantity;
                        const newSubtotal = price * newQuantity;

                        // Update products array
                        existingProduct.quantity = newQuantity;

                        // Update table row
                        const tableBody = document.querySelector('#productTable tbody');
                        const rows = tableBody.querySelectorAll('tr');
                        const row = rows[existingProductIndex];

                        // Update quantity and subtotal cells
                        row.querySelector('td:nth-child(3)').textContent = newQuantity;
                        row.querySelector('td:nth-child(4)').textContent = `Rp ${newSubtotal.toFixed(2)}`;
                    } else {
                        // Add new product
                        const tableBody = document.querySelector('#productTable tbody');
                        const row = document.createElement('tr');
                        row.dataset.productId = productId;

                        row.innerHTML = `
                <td>${productName}</td>
                <td>Rp ${price.toFixed(2)}</td>
                <td>${quantity}</td>
                <td>Rp ${subtotal.toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-product">Remove</button></td>
            `;

                        tableBody.appendChild(row);

                        // Add the product to the products array
                        products.push({
                            product_id: productId,
                            quantity: quantity,
                            price: price
                        });

                        // Add event listener for the remove button
                        row.querySelector('.remove-product').addEventListener('click', function() {
                            const index = Array.from(tableBody.children).indexOf(row);
                            if (index > -1) {
                                products.splice(index, 1);
                            }
                            row.remove();
                            updateTotalPrice();
                            updateProductsInput();
                        });
                    }

                    // Reset the product selection and quantity
                    productSelect.selectedIndex = 0;
                    document.getElementById('price').value = '';
                    document.getElementById('quantity').value = 1;

                    // Update the total price and products input
                    updateTotalPrice();
                    updateProductsInput();
                }
            }

            function updateProductsInput() {
                // Update the hidden input with the JSON string
                productsInput.value = JSON.stringify(products);
            }

            // Update total price when shipping is selected
            const shippingRadios = document.querySelectorAll('.shipping-radio');
            shippingRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Hide all input fields first
                    document.querySelectorAll('.shipping-value-input').forEach(input => {
                        input.style.display = 'none';
                    });

                    // Show input field for selected radio
                    if (this.checked) {
                        const inputDiv = this.closest('.form-check').querySelector(
                            '.shipping-value-input');
                        inputDiv.style.display = 'block';

                        // Update the shipping value when input changes
                        const valueInput = inputDiv.querySelector('input');
                        valueInput.addEventListener('input', function() {
                            radio.value = this.value;
                            updateTotalPrice();
                        });
                    }
                    updateTotalPrice();
                });
            });

            function updateTotalPrice() {
                const rows = document.querySelectorAll('#productTable tbody tr');
                let totalPrice = 0;

                rows.forEach(row => {
                    const amount = parseFloat(row.children[3].textContent.replace('Rp ', '').replace(/,/g,
                        '')) || 0;
                    totalPrice += amount;
                });

                document.getElementById('totalPrice').textContent = `Rp ${totalPrice.toFixed(2)}`;

                // Get selected shipping value
                const selectedShipping = document.querySelector('input[name="shipping_id"]:checked');
                const shippingValue = selectedShipping ? parseFloat(selectedShipping.value) : 0;
                document.getElementById('shipping_cost').value = shippingValue.toFixed(2);

                const finalPrice = totalPrice + shippingValue;

                // Ensure final price is not less than 0
                document.getElementById('finalPrice').innerText = 'Rp ' + Math.max(finalPrice, 0).toLocaleString();
                document.getElementById('final_price').value = Math.max(finalPrice, 0);
            }

            // Attach the addProduct function to the button click event
            document.getElementById('addProduct').addEventListener('click', addProduct);

            // Toggle shipping section visibility
            const addShippingBtn = document.getElementById('addShippingBtn');
            if (addShippingBtn) {
                addShippingBtn.addEventListener('click', function() {
                    const shippingSection = document.getElementById('shippingSection');
                    shippingSection.style.display = shippingSection.style.display === 'none' ? 'block' :
                        'none';
                });
            }

            // Update total price when shipping input changes
            const shippingCost = document.getElementById('shipping_cost');
            if (shippingCost) {
                shippingCost.addEventListener('input', updateTotalPrice);
            }
        });

        function showConfirmation() {
            const form = document.getElementById('purchaseForm');

            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Validate product table
            const productTable = document.querySelector('#productTable tbody');
            if (productTable.children.length === 0) {
                alert('Please add at least one product to the purchase.');
                return;
            }

            // Check for invalid quantities
            const rows = productTable.querySelectorAll('tr');
            let hasInvalidQuantity = false;
            rows.forEach(row => {
                const quantity = parseInt(row.children[2].textContent);
                if (quantity <= 0) {
                    hasInvalidQuantity = true;
                }
            });

            if (hasInvalidQuantity) {
                alert("All products must have a positive quantity");
                return;
            }
            // Get selected warehouse option
            const multiWarehouseRadio = document.getElementById('multiWarehouse');
            const warehouseSelect = document.getElementById('warehouse_id');

            // Validate warehouse selection if multi-warehouse is selected
            if (multiWarehouseRadio && multiWarehouseRadio.checked && warehouseSelect && !warehouseSelect.value) {
                alert('Please select a warehouse first!');
                return;
            }

            // Show the confirmation modal with a generic message
            document.getElementById('customModal').style.display = 'block';
        }

        function hideConfirmation() {
            document.getElementById('customModal').style.display = 'none';
        }

        function submitForm() {
            hideConfirmation();
            document.getElementById('purchaseForm').submit();
        }
    </script>
@endsection
