@extends('layouts/conquer');

@section('content')
    <form method="POST" action="{{ route('warehouse.update', $data->id) }}">
        @csrf
        @method('PUT')

        <div class="form-group">
            <label for="name">Warehouse Name</label>
            <input type="text" class="form-control" name="name" aria-describedby="typeHelp"
                placeholder="Enter warehouse name" value="{{ $data->name }}">
        </div>

        <div class="form-group">
            <label for="name">Warehouse Address</label>
            <input type="text" class="form-control" name="address" aria-describedby="typeHelp"
                placeholder="Enter warehouse address" value="{{ $data->address }}">
        </div>

        <a class ="btn btn-info" href="{{ url()->previous() }}"> Cancel </a>
        <button type="submit" class="btn btn-primary">Submit</button>


    </form>
@endsection
