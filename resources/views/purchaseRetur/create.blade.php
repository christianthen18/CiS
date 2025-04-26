@extends('layouts.conquer')

@section('content')
    <div class="container">
        <h1>Return Product Form</h1>
        <form action="{{ route('purchaseRetur.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="invoice_number">Select Invoice</label>
                <select class="form-control" name="invoice_number" id="invoice_number" required>
                    <option value="">Select an invoice</option>
                    @foreach ($purchases as $purchase)
                        <option value="{{ $purchase->id }}" data-date="{{ $purchase->purchase_date }}">
                            {{ $purchase->noNota }} <!-- Display noNota in the dropdown -->
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
            </div>

            <div class="form-group">
                <label for="refund_amount">Refund Amount</label>
                <input type="number" class="form-control" name="refund_amount" id="refund_amount" required readonly>
            </div>

            <button type="submit" class="btn btn-primary">Submit Return</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('invoice_number').addEventListener('change', function() {
                var selectedOption = this.options[this.selectedIndex];
                var purchaseDate = selectedOption.getAttribute('data-date');

                // Extract only the date part (yyyy-MM-dd)
                var dateOnly = purchaseDate.split(' ')[0]; // Get the date part only

                // Fill in the purchase date
                document.getElementById('purchase_date').value = dateOnly;

                // Clear previous product options
                var productSelect = document.getElementById('product_id');
                productSelect.innerHTML = '<option value="">Select a product</option>';

                // Reset total quantity and total price
                document.getElementById('total_quantity').value = '';
                document.getElementById('total_price').value = '';

                // Fetch products for the selected purchase_id
                var purchaseId = selectedOption.value; // Get the selected purchase ID
                fetch(`/purchase/${purchaseId}/details`) // Adjust the route as necessary
                    .then(response => response.json())
                    .then(data => {
                        data.forEach(function(detail) {
                            var productOption = document.createElement('option');
                            productOption.value = detail.product_id;
                            productOption.textContent = detail.product
                                .name; // Assuming product has a name property
                            productSelect.appendChild(productOption);
                        });
                    })
                    .catch(error => console.error("Error fetching products:", error));
            });

            // Update total price and quantity when a product is selected
            document.getElementById('product_id').addEventListener('change', function() {
                var selectedProductId = this.value; // Get the selected product ID
                var purchaseId = document.getElementById('invoice_number')
                    .value; // Get the selected purchase ID

                // Fetch product details for the selected product
                fetch(
                        `/purchase/${purchaseId}/details/${selectedProductId}`
                    ) // Adjust the route as necessary
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('total_quantity').value = data
                            .quantity; // Set total quantity
                        document.getElementById('total_price').value = data
                            .subtotal_price; // Set price from product
                    })
                    .catch(error => console.error("Error fetching product details:", error));
            });

            // Update refund amount when return quantity is changed
            document.getElementById('return_quantity').addEventListener('input', function() {
                var returnQuantity = parseFloat(this.value) || 0; // Get the return quantity
                var totalQuantity = parseFloat(document.getElementById('total_quantity').value) ||
                0; // Get the total quantity

                // Check if return quantity exceeds total quantity
                if (returnQuantity > totalQuantity) {
                    alert(
                    `Return quantity cannot exceed total quantity of ${totalQuantity}.`); // Alert the user
                    this.value = totalQuantity; // Set value to total quantity
                }

                // Calculate refund amount
                var totalPrice = parseFloat(document.getElementById('total_price').value) ||
                0; // Get the total price
                var refundAmount = returnQuantity * totalPrice;

                // Update the refund amount field
                document.getElementById('refund_amount').value = refundAmount.toFixed(
                2); // Format to 2 decimal places
            });

        });
    </script>
@endsection
