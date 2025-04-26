@extends('layouts.conquer')

@section('content')
    <div class="card">
        <div class="card-header">
            <form action="" method="get">
                <div class="card-body">
                    <h5 class="card-title">POS Cashier</h5>
                <div class="row align-items-center">

                    <div class="mb-3 col-3">
                        <label for="">Supplier</label>
                        <select name="supplier_id" class="form-control" id="">
                            <option value="">All</option>
                            @foreach ($supplier as $item)
                                <option value="{{ $item->id }}">{{ $item->company_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-3">
                        <label for="">Category</label>
                        <select name="category_id" class="form-control" id="">
                            <option value="">All</option>
                            @foreach ($categories as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3 col-6 mt-4">
                        <button class="btn btn-secondary btn-sm">Apply Filter</button>
                    </div>
                </div>
            </form>

        </div>
    </div>

    <div class="row mt-3">
        <!-- Products Section -->
        <div class="col-md-8 mb-4 mb-md-0">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">Products</h5>
                    <div class="row">
                        @foreach ($products as $d)
                            <div class="col-6 col-md-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <!-- Product Image -->
                                    <div class="card-img-top text-center">
                                        @if ($d->productImage)
                                            <img src="{{ asset('/images/' . $d->productImage->name) }}"
                                                class="img-fluid mt-1" alt="{{ $d->name }}"
                                                style="max-height: 100px; object-fit: cover; border-radius: 10px;">
                                        @else
                                            <img src="{{ asset('/images/no-image.png') }}" class="img-fluid img-thumbnail"
                                                alt="No image available" style="max-height: 200px; object-fit: cover;">
                                        @endif
                                    </div>
                                    <!-- Product Details -->
                                    <div class="card-body text-center">
                                        <h5 class="card-title">{{ $d->name }}</h5>
                                        <p class="card-text">
                                            <strong>Rp {{ number_format($d->price, 0, ',', '.') }}</strong>
                                        </p>
                                        <!-- Action Button -->
                                        <div class="text-center">
                                            <button class="btn btn-success btn-sm"
                                                onclick="addToCart({{ $d->id }}, '{{ $d->name }}', {{ $d->price }})">
                                                <i class="fas fa-cart-plus"></i> Add to Cart
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Cart Section -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="mb-3">
                            <h5 class="card-title">Cart</h5>
                        </div>
                        <div class="mb-3">
                            <div class="text-center">
                                <button id="checkout-btn" class="btn btn-primary" style="display: none;">Proceed to
                                    Checkout</button>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="row align-items-center">
                            <div class="col-10">
                                <label for="">Customer</label>
                                <select name="customer" class="form-control" id="customer_id">
                                    <option value="">Choose Customer</option>
                                    @foreach ($customers as $item)
                                        <option value="{{ $item->id }}">{{ $item->name }}-{{ $item->phone_number }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-2" style="margin-top: 30px">
                                <button type="button" id="addCustomerModal" class="btn btn-secondary btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#customerModal">
                                    <i class="fas fa-user-plus"></i>
                                </button>
                            </div>
                            <div class="col-12">
                                <label for="">Payment Method</label>
                                <select name="payment_method_id" class="form-control" id="payment_method_id">
                                    <option value="">Choose Payment Method</option>
                                    <option value="1">Cash</option>
                                    <option value="2">Transfer Bank</option>
                                    <option value="3">Credit Card</option>
                                </select>
                            </div>
                            <div class="discount-section mt-3">
                                <label class="form-label fw-bold mb-3">Discount</label>
                                <div class="discount-options">
                                    @foreach ($konfigurasi->where('name', 'Discount')->first()->details as $item)
                                        <div class="mb-2">
                                            <input type="radio" name="discount" value="{{ $item->value }}"
                                                class="discount_value" id="discount_{{ $item->id }}">
                                            <label for="discount_{{ $item->id }}">{{ $item->name }}</label>
                                            <div class="custom-discount-input mt-2 ms-4" style="display: none;">
                                                <input type="number" class="form-control form-control-sm custom-percentage"
                                                    placeholder="Enter percentage" min="0" max="100"
                                                    value="{{ $item->value }}" style="width: 150px;">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Cart Items -->
                    <div id="cart-items">
                        <p>Your cart is empty.</p>
                    </div>
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <strong>Total Price:</strong>
                            <span id="total-price">Rp 0</span>
                            <span id="total_temp" class="d-none"></span>
                        </div>
                    </div>

                    <!-- Discount Section -->
                    {{-- <div class="mt-3">
                        <label for="discount-input" class="form-label">Discount (Rp):</label>
                        <input type="number" id="discount-input" class="form-control"
                            placeholder="Enter discount in Rupiah">
                    </div> --}}

                    <!-- Shipping Cost Section -->
                    {{-- <div class="mt-3">
                        <label for="shipping-cost-input" class="form-label">Shipping Cost (Rp):</label>
                        <input type="number" id="shipping-cost-input" class="form-control"
                            placeholder="Enter shipping cost">
                    </div> --}}

                    <!-- Final Total Price -->
                    <div class="mt-3">
                        <div class="d-flex justify-content-between">
                            <strong>Final Total:</strong>
                            <span id="final-total">Rp 0</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="customerModal" tabindex="-1" aria-labelledby="customerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="customerModalLabel">Add New Customer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Modal content goes here -->
                    <form id="addCustomerForm" action="{{ route('customer.store.new') }}" method="post">
                        @csrf
                        <div class="mb-3">
                            <label for="customerName" class="form-label">Customer Name</label>
                            <input type="text" class="form-control" id="customerName" name="name"
                                placeholder="Enter customer name" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerEmail" class="form-label">Email</label>
                            <input type="email" class="form-control" id="customerEmail" placeholder="Enter email"
                                name="email" required>
                        </div>
                        <div class="mb-3">
                            <label for="customerPhone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="customerPhone" name="phone_number"
                                placeholder="Enter phone number" required>
                        </div>

                        <div class="mb-3">
                            <label for="customerPhone" class="form-label">Customer Address</label>
                            <textarea class="form-control" id="customerAddress" name="address" placeholder="Enter your address" required></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" form="addCustomerForm">Save Customer</button>
                </div>
            </div>
        </div>
    </div>


    <script>
        // Initialize global variables
        let checkoutBtn = document.getElementById('checkout-btn');
        let totalDiscountTemplate = 0;
        let finalTotal = 0;

        // Load cart on window load
        window.onload = function() {
            loadCart();
        };

        // Cart Management Functions
        function addToCart(id, name, price) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];

            let existingItem = cart.find(item => item.id === id);
            if (existingItem) {
                existingItem.quantity++;
            } else {
                cart.push({
                    id,
                    name,
                    price,
                    quantity: 1
                });
            }

            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }

        function removeFromCart(id) {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart = cart.filter(item => item.id !== id);
            localStorage.setItem('cart', JSON.stringify(cart));
            loadCart();
        }

        function loadCart() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            let cartItemsContainer = document.getElementById('cart-items');
            let totalPrice = 0;

            cartItemsContainer.innerHTML = '';

            if (cart.length === 0) {
                cartItemsContainer.innerHTML = '<p>Your cart is empty.</p>';
                checkoutBtn.style.display = 'none';
            } else {
                cart.forEach(item => {
                    let itemHTML = `
                        <div class="card mb-3">
                            <div class="card-body text-center">
                                <h6>${item.name}</h6>
                                <p><strong>Rp ${item.price.toLocaleString()}</strong> x ${item.quantity}</p>
                                <button class="btn btn-danger btn-sm" onclick="removeFromCart(${item.id})">
                                    <i class="fas fa-trash"></i> Remove
                                </button>
                            </div>
                        </div>
                    `;
                    cartItemsContainer.innerHTML += itemHTML;
                    totalPrice += item.price * item.quantity;
                });

                checkoutBtn.style.display = 'block';
            }

            document.getElementById('total-price').textContent = 'Rp ' + totalPrice.toLocaleString();
            document.getElementById('total_temp').textContent = totalPrice;
            calculateFinalTotal(totalPrice);
        }

        // Discount Calculation Functions
        function calculateFinalTotal(totalPrice) {
            const discountTemplate = parseInt(document.querySelector('input[name="discount"]:checked')?.value || 0);

            totalDiscountTemplate = totalPrice * (discountTemplate / 100);
            finalTotal = totalPrice - totalDiscountTemplate;

            document.getElementById('total_temp').textContent = totalPrice;
            document.getElementById('final-total').textContent = 'Rp ' + finalTotal.toLocaleString();
        }

        // Event Listeners for Discount
        document.querySelectorAll('.discount_value').forEach((radio) => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.custom-discount-input').forEach(input => {
                    input.style.display = 'none';
                });

                if (this.checked) {
                    const customInput = this.closest('div').querySelector('.custom-discount-input');
                    customInput.style.display = 'block';

                    const percentageInput = customInput.querySelector('.custom-percentage');
                    percentageInput.value = this.value;
                }

                let totalPrice = parseInt(document.getElementById('total_temp').textContent || 0);
                calculateFinalTotal(totalPrice);
            });
        });

        document.querySelectorAll('.custom-percentage').forEach((input) => {
            input.addEventListener('input', function() {
                if (this.value > 100) this.value = 100;
                if (this.value < 0) this.value = 0;

                const radio = this.closest('div').parentElement.querySelector('input[type="radio"]');
                radio.value = this.value;

                const totalPrice = parseInt(document.getElementById('total_temp').textContent || 0);
                calculateFinalTotal(totalPrice);
            });
        });

        // Checkout Handler
        checkoutBtn.addEventListener('click', function() {
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            const totalPrice = cart.reduce((sum, item) => sum + item.price * item.quantity, 0);
            const customer_id = document.getElementById('customer_id').value;
            const payment_method_id = document.getElementById('payment_method_id').value;

            if (!customer_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Harap pilih customer terlebih dahulu!',
                    confirmButtonText: 'OK'
                });
                return;
            }

            if (!payment_method_id) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Harap pilih payment method terlebih dahulu!',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const data = {
                cart,
                discount: totalDiscountTemplate,
                shipping_cost: 0,
                total_price: totalPrice,
                final_total: finalTotal,
                customer_id,
                payment_method_id
            };

            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            fetch('/pos/store', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Pesanan Berhasil',
                            text: data.message || 'Pesanan telah berhasil diproses.',
                            confirmButtonText: 'OK'
                        }).then(() => {
                            localStorage.removeItem('cart');
                            window.open('/pos/' + data.data.id);
                        });
                    } else {
                        if (data.errors) {
                            let errorMessage = '';
                            for (const [field, messages] of Object.entries(data.errors)) {
                                errorMessage += `${field}: ${messages.join(', ')}\n`;
                            }
                            Swal.fire({
                                icon: 'error',
                                title: 'Validasi Gagal',
                                text: errorMessage,
                                confirmButtonText: 'Tutup'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Terjadi Kesalahan',
                                text: data.message || 'Silakan coba lagi.',
                                confirmButtonText: 'Tutup'
                            });
                        }
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal Mengirim Data',
                        text: 'Periksa koneksi Anda dan coba lagi.',
                        confirmButtonText: 'Tutup'
                    });
                });
        });
    </script>
@endsection
