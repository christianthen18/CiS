@extends('layouts/conquer');

@section('content') 
<form method="POST" action="{{ route('customer.update', $data->id) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">Customer Name</label>
        <input type="text" class="form-control" name="name" aria-describedby="typeHelp" placeholder="Enter customer's name" value="{{ $data -> name }}">

        <label for="address">Customer Address</label>
        <input type="text" class="form-control" name="address" aria-describedby="typeHelp" placeholder="Enter customer's address" value="{{ $data -> address }}">
        
        <label for="address">Customer Phone Number</label>
        <input type="text" class="form-control" name="phone_number" aria-describedby="typeHelp" placeholder="Enter customer's phone number" value="{{ $data -> phone_number }}">

        <label for="address">Customer Email</label>
        <input type="text" class="form-control" name="email" aria-describedby="typeHelp" placeholder="Enter customer's email" value="{{ $data -> email }}">
    
        <label for="status_active" class="mt-3">Status Active</label>
        <select class="form-control" name="status_active">
            <option value="1" {{ $data->status_active == 1 ? 'selected' : '' }}>Active</option>
            <option value="0" {{ $data->status_active == 0 ? 'selected' : '' }}>Inactive</option>
        </select>
        <small class="form-text text-muted">Choose whether this customers is active (1) or inactive (0)</small>
    </div>
    <a class ="btn btn-info" href="{{ url()->previous() }}"> Cancel </a>
    <button type="submit" class="btn btn-primary">Submit</button>
    
</form>
@endsection