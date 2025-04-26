@extends('layouts/conquer');

@section('content')
    <form method="POST" action="{{ route('product.update', $data->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Product Name</label>
            <input type="text" class="form-control" name="name" aria-describedby="typeHelp"
                placeholder="Enter product name" value="{{ $data->name }}">
        </div>

        <div class="form-group">
            <label for="name">Product Description</label>
            <input type="text" class="form-control" name="desc" aria-describedby="typeHelp"
                placeholder="Enter product description" value="{{ $data->desc }}">
        </div>

        <div class="form-group">
            <label for="name">Product Price</label>
            <input type="text" class="form-control" name="price" aria-describedby="typeHelp"
                placeholder="Enter product price" value="{{ $data->price }}">
        </div>

        <div class="form-group">
            <label for="name">Product Cost</label>
            <input type="text" class="form-control" name="cost" aria-describedby="typeHelp"
                placeholder="Enter product cost" value="{{ $data->cost }}">
        </div>

        <div class="form-group">
            <label for="name">Minimum Stock</label>
            <input type="text" class="form-control" name="minimum_stock" aria-describedby="typeHelp"
                placeholder="Enter your minimum stock" value="{{ $data->minimum_stock }}">
        </div>

        <div class="form-group">
            <label for="name">Maksimum Retur</label>
            <input type="text" class="form-control" name="maksimum_retur" aria-describedby="typeHelp"
                placeholder="Enter your maksimum retur" value="{{ $data->maksimum_retur }}">
        </div>

        <div class="form-group">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="status_active" name="status_active"
                    {{ $data->status_active ? 'checked' : '' }}>
                <label class="form-check-label" for="status_active">
                    Active
                </label>
            </div>
        </div>

        <a class ="btn btn-info" href="{{ url()->previous() }}"> Cancel </a>
        <button type="submit" class="btn btn-primary">Submit</button>


    </form>
@endsection
