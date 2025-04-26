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
                                @if ($customer->status_active == 1)
                                    <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                @endif
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Only active customers are displayed.</small>
                    </div>

                    <!-- Employee Selection -->
                    <div class="form-group">
                        <label for="employee_id">Employee Name:</label>
                        <input type="text" class="form-control" value="{{ Auth::user()->username }}" readonly>
                        <input type="hidden" name="sales_employes_id" value="{{ $employeId }}">
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
                                    <option value="{{ $product->id }}" data-price="{{ $product->price }}"
                                        data-stock="{{ $product->stock }}">
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
                    <div class="card mt-4">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#discountCollapse">
                            <h5 class="mb-0">Discount Options <i class="bi bi-chevron-down"></i></h5>
                        </div>
                        <div class="collapse" id="discountCollapse">
                            <div class="card-body">
                                @if ($activeDiscounts->isNotEmpty())
                                    @foreach ($activeDiscounts as $discount)
                                        <div class="form-check mb-3">
                                            <input class="form-check-input discount-radio" type="radio" name="discount_id"
                                                value="{{ $discount->value }}" id="discount{{ $discount->id }}"
                                                data-name="{{ $discount->name }}"
                                                data-min-value="{{ $discount->min_value ?? 0 }}">
                                            <label class="form-check-label" for="discount{{ $discount->id }}">
                                                {{ $discount->name }}
                                            </label>

                                            <!-- Add description text based on discount type -->
                                            @if (str_contains($discount->name, 'Discount product'))
                                                <small class="d-block ms-4 text-muted">Minimum 1 product</small>
                                            @elseif (str_contains($discount->name, 'Minimum purchase discount'))
                                                <small class="d-block ms-4 text-muted">Minimum
                                                    Rp.{{ number_format($discount->min_value ?? 2000000) }}</small>
                                            @elseif (str_contains($discount->name, 'Discount on the number of product purchases'))
                                                <small class="d-block ms-4 text-muted">Minimum
                                                    {{ $discount->min_value ?? 20 }} products</small>
                                            @endif



                                            <div class="ms-4 mt-2 discount-value-input" style="display: none;">
                                                <div class="input-group" style="max-width: 200px;">
                                                    <input type="number" class="form-control"
                                                        value="{{ $discount->value }}" min="0" max="100"
                                                        step="0.1">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                    <input type="hidden" name="sales_disc" id="sales_disc" value="0">
                                @else
                                    <p>No active discounts available.</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Shipping Section -->
                    <div class="card mt-4 ">
                        <div class="card-header" data-bs-toggle="collapse" data-bs-target="#shippingCollapse">
                            <h5 class="mb-0">Shipping Options <i class="bi bi-chevron-down"></i></h5>
                        </div>
                        <div class="collapse" id="shippingCollapse">
                            <div class="card-body">
                                <label for="shipped_date">Shipped Date</label>
                                <input type="date" class="form-control" name="sales_shipdate" id="shipped_date">
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

                    {{-- SECTION FINAL PRICE --}}
                    <div class="card mt-4 border-0" style="background-color: #f8f9fa;">
                        <div class="card-body text-center">
                            <h4 class="text-muted">Final Price</h4>
                            <h2 class="display-4 mb-0 text-primary" id="finalPrice">Rp 0</h2>
                            {{-- <input type="hidden" name="final_price" id="final_price"> --}}
                        </div>
                        <input type="hidden" name="final_price" id="final_price">

                    </div>

                    {{-- SECTION PAYMENT METHODS --}}
                    @if ($activePayments->isNotEmpty())
                        <div class="form-group">
                            <label for="payment_methods_id">Payment Method</label>
                            <select class="form-control" name="payment_methods_id" id="payment_methods_id" required>
                                <option value="">Select Payment Method</option>
                                @foreach ($activePayments as $paymentMethod)
                                    <option value="{{ $paymentMethod->id }}">{{ $paymentMethod->name }}</option>
                                @endforeach
                            </select>
                            <small id="paymentMethodHelp" class="form-text text-muted">Please select a payment
                                method</small>
                        </div>

                        <!-- Add the card number field - initially hidden -->
                        <div class="form-group" id="card_number_container" style="display: none;">
                            <label for="card_number">Credit Card Number</label>
                            <input type="text" class="form-control" id="card_number" name="card_number"
                                maxlength="16" placeholder="Enter 16-digit card number">
                            <small class="form-text text-muted">Please enter your 16-digit credit card number without
                                spaces</small>
                        </div>
                    @else
                        <p>No active Payment available.</p>
                    @endif

                    <!-- COGS Method Section -->
                    <div class="form-group">
                        <label for="payment_methods_id">Select Cogs Method:</label>
                        <select class="form-control" name="cogs_method" required>
                            <option value="">Select COGS Method</option>
                            @foreach ($activeCogs as $cogs_method)
                                <option value="{{ $cogs_method->id }}">{{ $cogs_method->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <a class="btn btn-secondary me-2" href="{{ url()->previous() }}">Cancel</a>
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

        // Function to format numbers with Indonesian formatting (periods for thousands)
        function formatIDR(number) {
            return number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        }

        function getPrice(selectElement) {
            const priceInput = document.getElementById('price');
            const quantityInput = document.getElementById('quantity');
            const selectedOption = selectElement.options[selectElement.selectedIndex];
            const price = selectedOption.getAttribute('data-price') || 0;
            const stock = parseInt(selectedOption.getAttribute('data-stock') || 0);

            // Update price field
            priceInput.value = price;

            // Set the max quantity to stock
            quantityInput.max = stock;

            // Set min to 1 to prevent negative and zero values
            quantityInput.min = 1;

            // If current quantity exceeds stock or is less than 1, adjust it
            if (parseInt(quantityInput.value) > stock) {
                quantityInput.value = stock;
            } else if (parseInt(quantityInput.value) <= 0) {
                quantityInput.value = 1;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            const products = []; // Initialize an array to hold products
            const productsInput = document.getElementById('productsInput');
            const paymentMethodSelect = document.getElementById('payment_methods_id');
            const cardNumberContainer = document.getElementById('card_number_container');
            const cardNumberInput = document.getElementById('card_number');

            // Function to handle discount selection and disabling
            function manageDiscountOptions(selectedRadio = null) {
                const discountRadios = document.querySelectorAll('.discount-radio');

                if (selectedRadio) {
                    // Disable all other discount options
                    discountRadios.forEach(radio => {
                        if (radio !== selectedRadio) {
                            radio.disabled = true;
                            // Also add a visual indication
                            radio.closest('.form-check').classList.add('text-muted');
                        }
                    });

                    // Show reset button if it exists
                    if (document.getElementById('resetDiscountBtn')) {
                        document.getElementById('resetDiscountBtn').style.display = 'inline-block';
                    }
                } else {
                    // Enable all discount options
                    discountRadios.forEach(radio => {
                        radio.disabled = false;
                        radio.closest('.form-check').classList.remove('text-muted');
                    });

                    // Hide reset button if it exists
                    if (document.getElementById('resetDiscountBtn')) {
                        document.getElementById('resetDiscountBtn').style.display = 'none';
                    }
                }
            }

            // Function to toggle the credit card number field
            function toggleCardNumberField() {
                // Check for the exact payment method "S-Credit Card"
                const selectedOption = paymentMethodSelect.options[paymentMethodSelect.selectedIndex];
                const isCreditCard = selectedOption.text === 'S-Credit Card';

                // Show/hide card number field based on selection
                if (isCreditCard) {
                    cardNumberContainer.style.display = 'block';
                    cardNumberInput.required = true;
                } else {
                    cardNumberContainer.style.display = 'none';
                    cardNumberInput.required = false;
                    cardNumberInput.value = ''; // Clear the input when not used
                }
            }

            // Initial check when page loads
            if (paymentMethodSelect) {
                toggleCardNumberField();

                // Add event listener for changes to the payment method
                paymentMethodSelect.addEventListener('change', toggleCardNumberField);
            }

            // Add validation for credit card number (numbers only)
            if (cardNumberInput) {
                cardNumberInput.addEventListener('input', function() {
                    // Remove any non-numeric characters
                    this.value = this.value.replace(/[^\d]/g, '');

                    // Limit to 16 digits
                    if (this.value.length > 16) {
                        this.value = this.value.slice(0, 16);
                    }
                });
            }

            // Create and add reset button for discounts if it doesn't exist
            const discountHeader = document.querySelector(
                '.card-header[data-bs-toggle="collapse"][data-bs-target="#discountCollapse"]');
            if (discountHeader && !document.getElementById('resetDiscountBtn')) {
                // Check if it's not already a flex container
                if (!discountHeader.classList.contains('d-flex')) {
                    discountHeader.classList.add('d-flex', 'justify-content-between', 'align-items-center');

                    // Make sure the title is in its own container
                    const titleElement = discountHeader.querySelector('h5');
                    if (titleElement && titleElement.parentNode === discountHeader) {
                        titleElement.classList.add('mb-0');
                    }
                }

                const resetButton = document.createElement('button');
                resetButton.type = 'button';
                resetButton.id = 'resetDiscountBtn';
                resetButton.className = 'btn btn-sm btn-outline-secondary';
                resetButton.innerHTML = 'Reset Selection';
                resetButton.style.display = 'none'; // Initially hidden

                resetButton.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent toggling the collapse

                    // Uncheck all discount radios
                    document.querySelectorAll('.discount-radio').forEach(radio => {
                        radio.checked = false;
                    });

                    // Hide all discount value inputs
                    document.querySelectorAll('.discount-value-input').forEach(input => {
                        input.style.display = 'none';
                    });

                    // Re-enable all discount options
                    manageDiscountOptions();

                    // Update prices
                    updateTotalPrice();
                });

                discountHeader.appendChild(resetButton);
            }

            // Call updateTotalPrice on page load to ensure initial calculation
            updateTotalPrice();

            function addProduct() {
                const productSelect = document.getElementById('product_id');

                // Check if a product is selected
                if (!productSelect.value) {
                    alert("Please select a product");
                    return;
                }

                // Define variables explicitly with proper scope
                const selectedOption = productSelect.options[productSelect.selectedIndex];
                const productName = selectedOption.text;
                const price = parseFloat(document.getElementById('price').value) || 0;
                const quantity = parseInt(document.getElementById('quantity').value) || 1;
                const stock = parseInt(selectedOption.getAttribute('data-stock') || 0);

                // Validate against available stock
                if (quantity > stock) {
                    alert(`Cannot add more than available stock (${stock})`);
                    document.getElementById('quantity').value = stock;
                    return;
                }

                // Validate against zero or negative quantities
                if (quantity <= 0) {
                    alert("Quantity must be greater than zero");
                    document.getElementById('quantity').value = 1;
                    return;
                }

                // Calculate amount
                const amount = price * quantity;

                // Add to table
                const tableBody = document.querySelector('#productTable tbody');
                const row = document.createElement('tr');

                // Format with thousand separators
                row.innerHTML = `
                <td>${productName}</td>
                <td>Rp ${formatIDR(price.toFixed(0))}</td>
                <td>${quantity}</td>
                <td>Rp ${formatIDR(amount.toFixed(0))}</td>
                <td><button type="button" class="btn btn-danger btn-sm remove-product">Remove</button></td>
            `;

                tableBody.appendChild(row);

                // Add the product to the products array
                products.push({
                    product_id: productSelect.value,
                    product_name: productName,
                    quantity: quantity,
                    price: price
                });

                // Add event listener for the remove button
                const removeBtn = row.querySelector('.remove-product');
                removeBtn.addEventListener('click', function() {
                    row.remove();
                    // Remove the product from the products array based on row index
                    const index = Array.from(tableBody.children).indexOf(row);
                    if (index > -1) {
                        products.splice(index, 1);
                    }
                    updateProductsInput();
                    updateTotalPrice();
                });

                // Reset the product selection and quantity
                productSelect.selectedIndex = 0;
                document.getElementById('price').value = '';
                document.getElementById('quantity').value = 1;

                // Update the products input
                updateProductsInput();
                updateTotalPrice();
            }

            function updateProductsInput() {
                // Update the hidden input with the JSON string
                productsInput.value = JSON.stringify(products);
            }

            // Update total price when discount is selected
            const discountRadios = document.querySelectorAll('.discount-radio');
            discountRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Hide all input fields first
                    document.querySelectorAll('.discount-value-input').forEach(input => {
                        input.style.display = 'none';
                    });

                    // Show input field for selected radio
                    if (this.checked) {
                        const inputDiv = this.closest('.form-check').querySelector(
                            '.discount-value-input');
                        if (inputDiv) {
                            inputDiv.style.display = 'block';

                            // Update the discount value when input changes
                            const valueInput = inputDiv.querySelector('input');
                            if (valueInput) {
                                valueInput.addEventListener('input', function() {
                                    radio.value = this.value;
                                    updateTotalPrice();
                                });
                            }
                        }

                        // Disable other discount options when this one is selected
                        manageDiscountOptions(this);
                    } else {
                        // If unchecked, re-enable all options
                        manageDiscountOptions();
                    }
                    updateTotalPrice();
                });
            });

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
                        if (inputDiv) {
                            inputDiv.style.display = 'block';

                            // Update the shipping value when input changes
                            const valueInput = inputDiv.querySelector('input');
                            if (valueInput) {
                                valueInput.addEventListener('input', function() {
                                    radio.value = this.value;
                                    updateTotalPrice();
                                });
                            }
                        }
                    }
                    updateTotalPrice();
                });
            });

            function updateTotalPrice() {
                const rows = document.querySelectorAll('#productTable tbody tr');
                let totalPrice = 0;
                let totalQuantity = 0;
                let hasProducts = false;

                // Track quantity per product for volume discount
                const productQuantities = {};
                let maxSingleProductQuantity = 0;

                rows.forEach(row => {
                    // Get the amount text, remove "Rp " prefix and thousand separators
                    const amountText = row.children[3].textContent.replace('Rp ', '').replace(/\./g, '')
                        .replace(/,/g, '');
                    const amount = parseFloat(amountText) || 0;
                    const quantity = parseInt(row.children[2].textContent) || 0;
                    const productName = row.children[0].textContent;

                    // Track quantities per product
                    if (!productQuantities[productName]) {
                        productQuantities[productName] = 0;
                    }
                    productQuantities[productName] += quantity;

                    // Update max single product quantity
                    if (productQuantities[productName] > maxSingleProductQuantity) {
                        maxSingleProductQuantity = productQuantities[productName];
                    }

                    totalPrice += amount;
                    totalQuantity += quantity;
                    if (quantity >= 1) hasProducts = true;
                });

                // Display the total price without discount or shipping with thousand separators
                const totalPriceElement = document.getElementById('totalPrice');
                if (totalPriceElement) {
                    totalPriceElement.textContent = `Rp ${formatIDR(totalPrice.toFixed(0))}`;
                }

                // Default values for discount and shipping
                let discountAmount = 0;
                let shippingValue = 0;

                // Check if any discount is already manually selected
                const manuallySelectedDiscount = document.querySelector('.discount-radio:checked:not(:disabled)');

                // Only process automatic discount selection if there are discount radios available
                // and no discount is manually selected
                const discountRadios = document.querySelectorAll('.discount-radio');
                if (discountRadios.length > 0) {
                    // If no manual selection, apply automatic selection
                    if (!manuallySelectedDiscount) {
                        // Reset discount options state
                        manageDiscountOptions();

                        let perProductDiscount = document.querySelector('input[data-name="Discount product"]');
                        let minimumPurchaseRadio = document.querySelector(
                            'input[data-name="Minimum purchase discount"]');
                        let volumeDiscountRadio = document.querySelector(
                            'input[data-name="Discount on the number of product purchases"]');

                        // Get minimum values from data attributes
                        const minPurchaseAmount = parseFloat(minimumPurchaseRadio ? minimumPurchaseRadio
                            .getAttribute(
                                'data-min-value') : 1000000);
                        const minProductQuantity = parseInt(volumeDiscountRadio ? volumeDiscountRadio.getAttribute(
                            'data-min-value') : 15);

                        // Reset all radios
                        discountRadios.forEach(radio => {
                            radio.checked = false;
                            const valueInput = radio.closest('.form-check').querySelector(
                                '.discount-value-input');
                            if (valueInput) {
                                valueInput.style.display = 'none';
                            }
                        });

                        console.log('Discount eligibility check:', {
                            totalPrice: totalPrice,
                            minPurchaseAmount: minPurchaseAmount,
                            totalQuantity: totalQuantity,
                            maxSingleProductQuantity: maxSingleProductQuantity,
                            minProductQuantity: minProductQuantity,
                            'Price meets minimum?': totalPrice >= minPurchaseAmount,
                            'Any single product meets minimum quantity?': maxSingleProductQuantity >=
                                minProductQuantity,
                            'Product quantities': productQuantities
                        });

                        // Check conditions and apply highest applicable discount
                        let selectedRadio = null;

                        // NEW CONDITION: Check for volume discount based on both quantity AND minimum purchase amount
                        if (maxSingleProductQuantity >= minProductQuantity &&
                            totalPrice >= minPurchaseAmount &&
                            volumeDiscountRadio) {
                            volumeDiscountRadio.checked = true;
                            selectedRadio = volumeDiscountRadio;
                        }
                        // Check if the minimum purchase threshold is met
                        else if (totalPrice >= minPurchaseAmount && minimumPurchaseRadio) {
                            minimumPurchaseRadio.checked = true;
                            selectedRadio = minimumPurchaseRadio;
                        }
                        // Check if there are any products for the basic product discount
                        else if (hasProducts && perProductDiscount) {
                            perProductDiscount.checked = true;
                            selectedRadio = perProductDiscount;
                        }

                        // If a discount was automatically selected, show its input and disable others
                        if (selectedRadio) {
                            const valueInput = selectedRadio.closest('.form-check').querySelector(
                                '.discount-value-input');
                            if (valueInput) {
                                valueInput.style.display = 'block';
                            }

                            // Disable other options
                            manageDiscountOptions(selectedRadio);
                        }
                    }

                    // Get selected discount value
                    const selectedDiscount = document.querySelector('input[name="discount_id"]:checked');
                    const discountValue = selectedDiscount ? parseFloat(selectedDiscount.value) : 0;

                    // Calculate discount amount
                    discountAmount = (totalPrice * discountValue) / 100;

                    const salesDiscElement = document.getElementById('sales_disc');
                    if (salesDiscElement) {
                        salesDiscElement.value = discountAmount.toFixed(2);
                    }
                }

                // Get shipping value if shipping options exist
                const selectedShipping = document.querySelector('input[name="shipping_id"]:checked');
                if (selectedShipping) {
                    shippingValue = parseFloat(selectedShipping.value) || 0;

                    const shippingCostElement = document.getElementById('shipping_cost');
                    if (shippingCostElement) {
                        shippingCostElement.value = shippingValue.toFixed(2);
                    }
                }

                // Calculate final price
                const finalPrice = totalPrice - discountAmount + shippingValue;

                // Update final price elements with formatted numbers
                const finalPriceElement = document.getElementById('finalPrice');
                const finalPriceInput = document.getElementById('final_price');

                if (finalPriceElement) {
                    finalPriceElement.innerText = `Rp ${formatIDR(Math.max(finalPrice, 0).toFixed(0))}`;
                }

                if (finalPriceInput) {
                    finalPriceInput.value = Math.max(finalPrice, 0);
                }

                console.log('updateTotalPrice calculation:', {
                    totalPrice,
                    discountAmount,
                    shippingValue,
                    finalPrice: Math.max(finalPrice, 0)
                });
            }

            // Add event listeners for discount value inputs
            document.querySelectorAll('.discount-value-input input').forEach(input => {
                input.addEventListener('input', function() {
                    const radio = this.closest('.form-check').querySelector('.discount-radio');
                    if (radio) {
                        radio.value = this.value;
                        updateTotalPrice();
                    }
                });
            });

            // Attach the addProduct function to the button click event
            const addProductBtn = document.getElementById('addProduct');
            if (addProductBtn) {
                addProductBtn.addEventListener('click', addProduct);
            }

            // Handle form submission
            const form = document.querySelector('form');
            if (form) {
                form.addEventListener('submit', function(event) {
                    // Final update before submission
                    updateTotalPrice();
                    console.log('Final Price before submit:', document.getElementById('final_price').value);
                });
            }

            // Toggle collapsible sections if buttons exist
            const addDiscountBtn = document.getElementById('addDiscountBtn');
            if (addDiscountBtn) {
                addDiscountBtn.addEventListener('click', function() {
                    const discountSection = document.getElementById('discountSection');
                    if (discountSection) {
                        discountSection.style.display = discountSection.style.display === 'none' ? 'block' :
                            'none';
                    }
                });
            }

            const addShippingBtn = document.getElementById('addShippingBtn');
            if (addShippingBtn) {
                addShippingBtn.addEventListener('click', function() {
                    const shippingSection = document.getElementById('shippingSection');
                    if (shippingSection) {
                        shippingSection.style.display = shippingSection.style.display === 'none' ? 'block' :
                            'none';
                    }
                });
            }

            // Add event listeners for direct input changes if elements exist
            const salesDiscElement = document.getElementById('sales_disc');
            if (salesDiscElement) {
                salesDiscElement.addEventListener('input', updateTotalPrice);
            }

            const shippingCostElement = document.getElementById('shipping_cost');
            if (shippingCostElement) {
                shippingCostElement.addEventListener('input', updateTotalPrice);
            }
        });
    </script>
@endsection
