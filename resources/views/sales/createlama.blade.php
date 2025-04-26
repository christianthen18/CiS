@extends('layouts.conquer')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-4">Create Transaction</h2>

        <form method="POST" action="{{ route('sales.store') }}">
            @csrf
            <div class="card shadow-sm">
                <div class="card-body">
                    <!-- Invoice Section -->
                    <div class="form-group">
                        <label for="no_nota">Invoice</label>
                        <input type="text" class="form-control" name="no_nota"
                            value="INV{{ str_pad($newNumber, 4, '0', STR_PAD_LEFT) }}" readonly>
                        <small class="form-text text-muted">This is your invoice number.</small>
                    </div>

                    <!-- Customer Selection -->
                    <div class="form-group">
                        <label for="customer_id">Customer Name</label>
                        <select class="form-control" name="sales_cust_id" required>
                            <option value="">Select Customer</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Please select a customer.</small>
                    </div>

                    <!-- Employee Selection -->
                    <div class="form-group">
                        <label for="employee_id">Employee Name</label>
                        <select class="form-control" name="sales_employes_id" required>
                            <option value="">Select Employee</option>
                            @foreach ($employees as $employee)
                                <option value="{{ $employee->id }}">{{ $employee->name }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Please select an employee.</small>
                    </div>

                    <!-- Date Section -->
                    <div class="form-group">
                        <label for="sales_date">Date</label>
                        <input type="date" class="form-control" name="sales_date" required>
                        <small class="form-text text-muted">Please select the date of the transaction.</small>
                    </div>

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
                            <input type="number" class="form-control" name="price" id="price" readonly>
                        </div>
                        <div class="col-md-2">
                            <label for="quantity">Quantity</label>
                            <input type="number" class="form-control" name="quantity" id="quantity" value="1"
                                min="1">
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
                                <th>Amount</th>
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

                    <!-- Discount Section -->
                    @if ($activeDiscounts->isNotEmpty())
                        <div class="form-group mt-4">
                            <button type="button" class="btn btn-primary" id="addDiscountBtn">Add Discount</button>
                        </div>
                        <div id="discountSection" style="display: none;">
                            <h5>Select Discount:</h5>
                            @foreach ($activeDiscounts as $discount)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="discount_id"
                                        value="{{ $discount->id }}" id="discount{{ $discount->id }}">
                                    <label class="form-check-label" for="discount{{ $discount->id }}">
                                        {{ $discount->name }}
                                    </label>
                                </div>
                            @endforeach
                            <div class="form-group">
                                <label for="sales_disc">Discount</label>
                                <input type="number" class="form-control" name="sales_disc" id="sales_disc" value="0"
                                    min="0" step="0.01" oninput="updateTotalPrice()">
                                <small id="discountHelp" class="form-text text-muted">Enter the discount amount (if
                                    any)</small>
                            </div>
                        </div>
                    @else
                        <p>No active discounts available.</p>
                    @endif

                    <!-- Shipping Section -->
                    <h5 class="mt-4">Shipping Information</h5>
                    <div class="form-group">
                        <label for="shipped_date">Shipped Date</label>
                        <input type="date" class="form-control" name="sales_shipdate" id="shipped_date">
                    </div>

                    @if ($activeShippings->isNotEmpty())
                        <div class="form-group mt-4">
                            <button type="button" class="btn btn-primary" id="addShippingBtn">Add Shipping</button>
                        </div>
                        <div id="shippingSection" style="display: none;">
                            <h5>Select Shipping:</h5>
                            @foreach ($activeShippings as $shipping)
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="shipping_id"
                                        value="{{ $shipping->id }}" id="shipping{{ $shipping->id }}">
                                    <label class="form-check-label" for="shipping{{ $shipping->id }}">
                                        {{ $shipping->name }}
                                    </label>
                                </div>
                            @endforeach
                            <div class="form-group">
                                <label for="shipping_cost">Shipping</label>
                                <input type="number" class="form-control" name="shipping_cost" id="shipping_cost"
                                    value="0" min="0" step="0.01" oninput="updateTotalPrice()">
                                <small id="discountHelp" class="form-text text-muted">Enter the shipping amount (if
                                    any)</small>
                            </div>
                        </div>
                    @else
                        <p>No active shipping available.</p>
                    @endif

                    {{-- SECTION FINAL PRICE --}}
                    <h5 class="mt-4">Final Price After Discount and Shipping: <span id="finalPrice">Rp 0</span></h5>
                    <input type="hidden" name="final_price" id="final_price">

                    {{-- SECTION PAYMENT METHODS --}}
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

                    <div class="d-flex justify-content-end mt-4">
                        <a class="btn btn-info me-2" href="{{ url()->previous() }}">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection

@section('javascript')
    <script>
        let totalPrice = 0; // Initialize total price

        function getPrice(selectElement) {
            const priceInput = document.getElementById('price');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            priceInput.value = price;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const products = []; // Initialize an array to hold products
            const productsInput = document.getElementById('productsInput');

            function addProduct() {
                const productSelect = document.getElementById('product_id');
                const productName = productSelect.options[productSelect.selectedIndex].text;
                const price = parseFloat(document.getElementById('price').value) || 0;
                const quantity = parseInt(document.getElementById('quantity').value) || 1;
                const amount = price * quantity;

                if (productSelect.value) {
                    const tableBody = document.querySelector('#productTable tbody');
                    const row = document.createElement('tr');

                    row.innerHTML = `
                    <td>${productName}</td>
                    <td>Rp ${price.toFixed(2)}</td>
                    <td>${quantity}</td>
                    <td>Rp ${(amount).toFixed(2)}</td>
                    <td><button type="button" class="btn btn-danger btn-sm remove-product">Remove</button></td>
                `;

                    tableBody.appendChild(row);
                    updateTotalPrice();

                    // Add the product to the products array
                    products.push({
                        product_id: productSelect.value,
                        quantity: quantity,
                        price: price
                    });

                    // Add event listener for the remove button
                    row.querySelector('.remove-product').addEventListener('click', function() {
                        row.remove();
                        updateTotalPrice();
                        // Remove the product from the products array
                        const index = products.findIndex(p => p.product_id === productSelect.value);
                        if (index > -1) {
                            products.splice(index, 1);
                        }
                        updateProductsInput();
                    });

                    // Reset the product selection and quantity
                    productSelect.selectedIndex = 0;
                    document.getElementById('price').value = '';
                    document.getElementById('quantity').value = 1;

                    // Update the products input
                    updateProductsInput();
                }
            }

            function updateProductsInput() {
                // Update the hidden input with the JSON string
                productsInput.value = JSON.stringify(products);
            }

            function updateTotalPrice() {
                const rows = document.querySelectorAll('#productTable tbody tr');
                let totalPrice = 0;

                rows.forEach(row => {
                    const amount = parseFloat(row.children[3].textContent.replace('Rp ', '').replace(/,/g,
                        '')) || 0;
                    totalPrice += amount;
                });

                document.getElementById('totalPrice').textContent = `Rp ${totalPrice.toFixed(2)}`;

                // Get discount value from input
                const discount = parseFloat(document.getElementById('sales_disc').value) || 0;
                const shipping = parseFloat(document.getElementById('shipping_cost').value) || 0;

                const finalPrice = totalPrice - discount - shipping;

                // Ensure final price is not less than 0
                document.getElementById('finalPrice').innerText = 'Rp ' + Math.max(finalPrice, 0).toLocaleString();
                document.getElementById('final_price').value = Math.max(finalPrice, 0);
            }

            // Attach the addProduct function to the button click event
            document.getElementById('addProduct').addEventListener('click', addProduct);

            // Handle form submission
            document.querySelector('form').addEventListener('submit', function(event) {
                // Debugging
                console.log('Final Price before submit:', document.getElementById('final_price').value);
            });

            // Toggle discount section visibility
            document.getElementById('addDiscountBtn').addEventListener('click', function() {
                const discountSection = document.getElementById('discountSection');
                discountSection.style.display = discountSection.style.display === 'none' ? 'block' : 'none';
            });

            // Toggle shipping section visibility
            document.getElementById('addShippingBtn').addEventListener('click', function() {
                const shippingSection = document.getElementById('shippingSection');
                shippingSection.style.display = shippingSection.style.display === 'none' ? 'block' : 'none';
            });

            // Update total price when discount input changes
            document.getElementById('sales_disc').addEventListener('input', updateTotalPrice);

            // Update total price when shipping input changes
            document.getElementById('shipping_cost').addEventListener('input', updateTotalPrice);
        });
    </script>
@endsection
