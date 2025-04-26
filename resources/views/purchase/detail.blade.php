    @extends('layouts.conquer')

    @section('content')
        <div class="container mt-5">
            <h2 class="text-center mb-4">Transaction Details - {{ $purchase->noNota }}</h2>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h5><strong>Supplier:</strong> {{ $purchase->supplier->company_name}}</h5>
                    <h5><strong>Warehouse:</strong> {{ $purchase->warehouse ? $purchase->warehouse->name : 'Directly in store' }}</h5> <!-- Display warehouse name or N/A -->  
                    <h5><strong>Total Price:</strong> Rp {{ number_format($purchase->total_price, 0, ',', '.') }}</h5>
                    <h5><strong>Payment Method:</strong> {{ $purchase->paymentMethod->name }}</h5>
                    <h5><strong>Purchase Date:</strong> {{ $purchase->purchase_date }}</h5>
                    <h5><strong>Receive Date:</strong> {{ $purchase->receive_date }}</h5>
                    <h5><strong>Shipping Cost:</strong> Rp {{ number_format($purchase->shipping_cost, 0, ',', '.') }}</h5>

                    <h5 class="mt-4">Products:</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Product Name</th>
                                <th>Quantity</th>
                                <th>Subtotal Price</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($purchase->purchaseDetails->isNotEmpty())
                                @foreach ($purchase->purchaseDetails as $detail)
                                    <tr>
                                        <td>{{ $detail->product->name }}</td>
                                        <td>{{ $detail->quantity }}</td>
                                        <td>Rp {{ number_format($detail->subtotal_price, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="3" class="text-center">No products found for this purchase.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>

                    <div class="text-center mt-4">
                        <a href="{{ route('purchase.index') }}" class="btn btn-info">Back to Purchases</a>
                    </div>
                </div>
            </div>
        </div>
    @endsection
