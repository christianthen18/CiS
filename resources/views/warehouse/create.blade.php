@extends('layouts.conquer')

@section('content')
    <form method="POST" action="{{ route('warehouse.store') }}">
        @csrf

        <div class="form-group">
            <label for="cust_name">Warehouse Name</label>
            <input type="text" class="form-control" name="warehouse_name" aria-describedby="nameHelp"
                placeholder="Enter Your Warehouse Name">
            <small id="nameHelp" class="form-text text-muted">Please enter your warehouse name</small>
        </div>

        <div class="form-group">
            <label for="cust_name">Warehouse Address</label>
            <input type="text" class="form-control" name="warehouse_address" aria-describedby="nameHelp"
                placeholder="Enter Your Warehouse Address">
            <small id="nameHelp" class="form-text text-muted">Please enter your warehouse address</small>
        </div>
        <a class="btn btn-info" href="{{ url()->previous() }}">Cancel</a>
        <button type="submit" class="btn btn-primary">Submit</button>
    </form>
@endsection
