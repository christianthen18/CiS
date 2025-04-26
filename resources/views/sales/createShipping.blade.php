@extends('layouts.conquer')

@section('content')
    <div class="container">
        <h1>Create Shipping</h1>
        <form action="{{ route('products.create-shipping') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="product_id">Product</label>
                <select name="product_id" class="form-control" required>
                    @foreach ($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group">
                <label for="quantity_shipped">Quantity</label>
                <input type="number" name="quantity_shipped" class="form-control" min="1" required>
            </div>
            <button type="submit" class="btn btn-primary">Ship</button>
        </form>
    </div>
@endsection
