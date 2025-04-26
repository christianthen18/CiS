@extends('layouts/conquer');

@section('content') 
<form method="POST" action="{{ route('suppliers.update', $data->id) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">Company Name</label>
        <input type="text" class="form-control" name="company_name" aria-describedby="typeHelp" placeholder="Enter company's name" value="{{ $data -> company_name }}">

        <label for="address">Company Phone Number</label>
        <input type="text" class="form-control" name="phone_number" aria-describedby="typeHelp" placeholder="Enter company's phone number" value="{{ $data -> phone_number }}">
        
        <label for="address">Company Email</label>
        <input type="text" class="form-control" name="email" aria-describedby="typeHelp" placeholder="Enter company's email" value="{{ $data -> email }}">
        
        <label for="address">Company Address</label>
        <input type="text" class="form-control" name="address" aria-describedby="typeHelp" placeholder="Enter customer's address" value="{{ $data -> address }}">

    </div>
    <a class ="btn btn-info" href="{{ url()->previous() }}"> Cancel </a>
    <button type="submit" class="btn btn-primary">Submit</button>

    
</form>
@endsection