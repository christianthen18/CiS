@extends('layouts.conquer')

@section('content')
    <div class="container">
        <h1>Return Product Form</h1>
        <form action="{{ route('salesRetur.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="customer_id">Select Customer</label>
                <select class="form-control" name="customer_id" id="customer_id" required>
                    <option value="">Select a customer</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}" data-phone="{{ $customer->phone_number }}"
                            data-address="{{ $customer->address }}" data-email="{{ $customer->email }}">
                            {{ $customer->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="customer_phone">Customer Phone</label>
                <input type="text" class="form-control" name="customer_phone" id="customer_phone" required readonly>
            </div>
            <div class="form-group">
                <label for="customer_address">Customer Address</label>
                <input type="text" class="form-control" name="customer_address" id="customer_address" required readonly>
            </div>
            <div class="form-group">
                <label for="customer_email">Customer Email</label>
                <input type="email" class="form-control" name="customer_email" id="customer_email" required readonly>
            </div>
            <div class="form-group">
                <label for="invoice_number">Select Invoice</label>
                <select class="form-control" name="invoice_number" id="invoice_number" required>
                    <option value="">Select an invoice</option>
                    @foreach ($sales as $sale)
                        <!-- Assuming $sales contains the sales records -->
                        <option value="{{ $sale->id }}" data-date="{{ $sale->date }}"
                            data-sales-id="{{ $sale->id }}">
                            {{ $sale->noNota }} <!-- Display noNota in the dropdown -->
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="purchase_date">Purchase Date</label>
                <input type="date" class="form-control" name="purchase_date" id="purchase_date" required readonly>
            </div>
            <div class="form-group">
                <label for="product_id">Select Product</label>
                <select class="form-control" name="product_id" id="product_id" required>
                    <option value="">Select a product</option>
                </select>
            </div>
            <div class="form-group">
                <label for="total_quantity">Total Quantity</label>
                <input type="number" class="form-control" name="total_quantity" id="total_quantity" required readonly>
            </div>
            <div class="form-group">
                <label for="total_price">Unit Price</label>
                <input type="number" class="form-control" name="total_price" id="total_price" required readonly>
            </div>
            <div class="form-group">
                <label for="retur_desc">Return Description</label>
                <input type="text" class="form-control" name="retur_desc" id="retur_desc"
                    placeholder="Enter return description" required>
            </div>
            <div class="form-group">
                <label for="return_quantity">Return Quantity</label>
                <input type="number" class="form-control" name="return_quantity" id="return_quantity" required>
                <small id="max_return_label" class="form-text text-muted">Maximum Return Quantity: <span
                        id="max_return_value">0</span></small>
            </div>
            <!-- Hidden input for maximum return quantity -->
            <input type="hidden" id="max_return_quantity" name="max_return_quantity">


            <div class="form-group">
                <label for="refund_amount">Refund Amount</label>
                <input type="number" class="form-control" name="refund_amount" id="refund_amount" required readonly>
            </div>
            <button type="submit" class="btn btn-primary">Submit Return</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log("DOM fully loaded and parsed");

            // Customer selection event
            document.getElementById('customer_id').addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var phone = selectedOption.getAttribute('data-phone');
                var address = selectedOption.getAttribute('data-address');
                var email = selectedOption.getAttribute('data-email');

                // Fill in the customer details
                document.getElementById('customer_phone').value = phone;
                document.getElementById('customer_address').value = address;
                document.getElementById('customer_email').value = email;

                // Clear previous purchase date
                document.getElementById('purchase_date').value = '';

                // Clear previous product options
                var productSelect = document.getElementById('product_id');
                productSelect.innerHTML = '<option value="">Select a product</option>';

                // Reset total quantity and total price
                document.getElementById('total_quantity').value = '';
                document.getElementById('total_price').value = '';

                // Autofill invoice options based on the selected customer
                var customerId = selectedOption.value;
                var salesByCustomer = @json($salesByCustomer); // Pass sales data to JavaScript

                // Clear previous invoice options
                var invoiceSelect = document.getElementById('invoice_number');
                invoiceSelect.innerHTML = '<option value="">Select an invoice</option>';

                // Populate invoice options
                if (salesByCustomer[customerId]) {
                    salesByCustomer[customerId].forEach(function(sale) {
                        var option = document.createElement('option');
                        option.value = sale.id; // Use sale.id for sales_id
                        option.textContent = sale.noNota;
                        option.setAttribute('data-sales-id', sale
                        .id); // Store sales_id in data attribute
                        option.setAttribute('data-date', sale.date.split(' ')[
                        0]); // Store date in data attribute
                        invoiceSelect.appendChild(option);
                    });
                }
            });

            document.getElementById('invoice_number').addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var purchaseDate = selectedOption.getAttribute('data-date');
                var salesId = selectedOption.getAttribute('data-sales-id');

                // Fill in the purchase date
                document.getElementById('purchase_date').value = purchaseDate;

                // Clear previous product options
                var productSelect = document.getElementById('product_id');
                productSelect.innerHTML = '<option value="">Select a product</option>';

                // Reset total quantity and total price
                document.getElementById('total_quantity').value = '';
                document.getElementById('total_price').value = '';

                // Fetch products for the selected sales_id
                var products = @json($productsBySale); // Pass products by sale to JavaScript

                // Populate product options based on the selected sales_id
                if (products[salesId]) {
                    products[salesId].forEach(function(detail) {
                        var productOption = document.createElement('option');
                        productOption.value = detail.product_id;
                        productOption.textContent = detail.product
                        .name; // Assuming product has a name property
                        productSelect.appendChild(productOption);
                    });
                } else {
                    console.error("No products found for sales ID:", salesId);
                }
            });

            // Update total price and maximum return quantity when a product is selected
            document.getElementById('product_id').addEventListener('change', function() {
                var selectedProductId = this.value; // Get the selected product ID
                var salesId = document.getElementById('invoice_number').value; // Get the selected sales ID
                var products = @json($productsBySale); // Pass products by sale to JavaScript

                // Clear previous quantity and price
                document.getElementById('total_quantity').value = '';
                document.getElementById('total_price').value = '';

                // Reset any previous error messages
                var returDescriptionInput = document.getElementById('retur_desc');
                var submitButton = document.querySelector('button[type="submit"]');
                var maxReturnLabel = document.getElementById('max_return_label');
                maxReturnLabel.innerHTML = 'Maximum Return Quantity: <span id="max_return_value">0</span>';
                returDescriptionInput.value = ''; // Clear previous description
                returDescriptionInput.disabled = false; // Enable description input
                returDescriptionInput.placeholder = 'Enter return description'; // Reset placeholder
                submitButton.disabled = false;

                // Find the selected product's details
                if (products[salesId]) {
                    var selectedProduct = products[salesId].find(function(detail) {
                        return detail.product_id == parseInt(selectedProductId); // Match product ID
                    });

                    // Log the selected product for debugging
                    console.log("Selected Product:", selectedProduct); // Debugging line

                    // Update total quantity and price field if the product is found
                    if (selectedProduct) {
                        document.getElementById('total_quantity').value = selectedProduct
                        .total_quantity; // Set total quantity
                        document.getElementById('total_price').value = selectedProduct.product
                        .price; // Set price from product

                        // Fetch maximum return quantity for the selected product
                        fetch(`/get-product-max-return?product_id=${selectedProductId}`)
                            .then(response => response.json())
                            .then(data => {
                                console.log("Fetched maximum return data:", data); // Debugging line

                                if (data.maksimum_retur !== undefined) {
                                    // Ensure maksimum_retur is a valid number
                                    var maksimumRetur = parseFloat(data.maksimum_retur);
                                    console.log("Maximum Return Quantity from Product:",
                                    maksimumRetur); // Debugging line

                                    if (isNaN(maksimumRetur) || maksimumRetur < 0) {
                                        maksimumRetur = 0; // Fallback to 0 if invalid
                                    }

                                    // Check if maksimum_retur is 0
                                    if (maksimumRetur === 0) {
                                        // Disable form submission and show error message
                                        var returDescriptionInput = document.getElementById(
                                            'retur_desc');
                                        var submitButton = document.querySelector(
                                            'button[type="submit"]');
                                        var maxReturnLabel = document.getElementById(
                                        'max_return_label');

                                        maxReturnLabel.innerHTML =
                                            '<span class="text-danger">The product cannot be returned</span>';
                                        returDescriptionInput.value = ''; // Clear the description
                                        returDescriptionInput.placeholder =
                                            'The product cannot be returned';
                                        returDescriptionInput.disabled = true;
                                        submitButton.disabled = true;

                                        // Reset other fields
                                        document.getElementById('return_quantity').value = '';
                                        document.getElementById('refund_amount').value = '';
                                        document.getElementById('max_return_quantity').value = 0;
                                        document.getElementById('max_return_value').textContent = '0';

                                        return; // Stop further processing
                                    }

                                    // Update the maximum return quantity based on total_quantity and maksimum_retur
                                    var totalQuantity = parseFloat(selectedProduct.total_quantity);
                                    var maxReturnQuantity = Math.min(totalQuantity, maksimumRetur);
                                    console.log("Calculated Max Return Quantity:",
                                    maxReturnQuantity); // Debugging line
                                    document.getElementById('max_return_quantity').value =
                                        maxReturnQuantity; // Store max return quantity
                                    document.getElementById('max_return_value').textContent =
                                        maxReturnQuantity; // Display max return quantity
                                } else {
                                    console.error("Error fetching maximum return quantity:", data
                                    .error);
                                }
                            })
                            .catch(error => console.error("Error:", error));
                    }
                }
            });

            // Update refund amount when return quantity is changed
            const returnQuantityInput = document.getElementById('return_quantity');
            const maxReturnQuantityInput = document.getElementById(
            'max_return_quantity'); // Get the max return quantity input
            const maxReturnLabel = document.getElementById(
            'max_return_value'); // Get the span to display max return quantity

            if (returnQuantityInput) {
                returnQuantityInput.addEventListener('input', function() {
                    var returnQuantity = parseFloat(this.value) || 0; // Get the return quantity
                    var totalPrice = parseFloat(document.getElementById('total_price').value) ||
                    0; // Get the total price
                    var maxReturnQuantity = parseFloat(maxReturnQuantityInput.value) ||
                    0; // Get the maximum return quantity

                    // Update the displayed maximum return quantity
                    maxReturnLabel.textContent = maxReturnQuantity;

                    // Check if return quantity exceeds maximum return quantity
                    if (returnQuantity > maxReturnQuantity) {
                        alert(`Return quantity cannot exceed ${maxReturnQuantity}.`); // Alert the user
                        this.value = maxReturnQuantity; // Set value to maximum return quantity
                        returnQuantity = maxReturnQuantity; // Update return quantity to max
                    }

                    // Calculate refund amount
                    var refundAmount = returnQuantity * totalPrice;

                    // Update the refund amount field
                    document.getElementById('refund_amount').value = refundAmount.toFixed(
                    2); // Format to 2 decimal places
                });
            } else {
                console.error("Element with ID 'return_quantity' not found.");
            }
        });
    </script>
@endsection
