@extends('layouts.conquer')

@section('content')
<div class="page-content">
    <h3 class="page-title">Upload Image for Product: {{ $product->name }}</h3>
    <div class="container">
        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form method="POST" enctype="multipart/form-data" action="{{ url('product/simpanPhoto') }}">
            @csrf
            <div class="form-group">
                <label for="file_photo">Choose Image</label>
                <input type="file" class="form-control" name="file_photo" required />
                
                <!-- Hidden input for product ID -->
                <input type="hidden" name="product_id" value="{{ $product->id }}" />
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
    </div>
</div>
@endsection
