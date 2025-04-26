@extends('layouts.conquer')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-4">Transaction Details - {{ $sale->noNota }}</h2>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5><strong>Customer:</strong> {{ $sale->customer->name }}</h5>
                <h5><strong>Total Price:</strong> Rp {{ number_format($sale->total_price, 0, ',', '.') }}</h5>
                <h5><strong>Payment Method:</strong> {{ $sale->paymentMethod->name }}</h5>

                @if ($sale->card_number)
                    <h5><strong>Card Number:</strong> {{ $sale->card_number }}</h5>
                @endif

                <h5><strong>Date:</strong> {{ $sale->date }}</h5>
                <h5><strong>Shipped Date:</strong> {{ $sale->shipped_date }}</h5>
                <h5><strong>Discount:</strong> Rp {{ number_format($sale->discount, 0, ',', '.') }}</h5>
                <h5><strong>Shipping Cost:</strong> Rp {{ number_format($sale->shipping_cost, 0, ',', '.') }}</h5>
                <h5><strong>Created At:</strong> {{ $sale->created_at }}</h5>
                <h5><strong>Updated At:</strong> {{ $sale->updated_at }}</h5>

                <h5 class="mt-4">Products:</h5>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Product Name</th>
                            <th>Quantity</th>
                            <th>Total Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if ($sale->salesDetail->isNotEmpty())
                            @foreach ($sale->salesDetail as $detail)
                                <tr>
                                    <td>{{ $detail->product->name }}</td>
                                    <td>{{ $detail->total_quantity }}</td>
                                    <td>Rp {{ number_format($detail->total_price, 0, ',', '.') }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="3" class="text-center">No products found for this sale.</td>
                            </tr>
                        @endif
                    </tbody>

                </table>

                <div class="text-center mt-4">
                    <a href="{{ route('pos.print', ['id' => $sale->id]) }}" target="_blank" class="btn btn-primary">Print
                        Receipt</a>
                    <a href="{{ route('sales.index') }}" class="btn btn-info">Back to Transactions</a>
                </div>
            </div>
        </div>
    </div>
@endsection
