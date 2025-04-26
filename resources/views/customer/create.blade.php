@extends('layouts.conquer')

@section('content') 
<form method="POST" action="{{ route('customer.store') }}">
    @csrf
    <div class="form-group">
        <label for="cust_name">Customer Name</label>
        <input type="text" class="form-control" name="cust_name" aria-describedby="nameHelp" placeholder="Enter Your Name">
        <small id="nameHelp" class="form-text text-muted">Please enter your name</small>
    </div>

    <div class="form-group">
        <label for="cust_address">Address</label>
        <input type="text" class="form-control" name="cust_address" aria-describedby="addressHelp" placeholder="Enter Your Address">
        <small id="addressHelp" class="form-text text-muted">Please enter your address</small>
    </div>

    <div class="form-group">
        <label for="user_id_cust">Phone Number</label>
        <input type="text" class="form-control" name="cust_phone" aria-describedby="userIdHelp" placeholder="Enter Phone Number">
        <small id="userIdHelp" class="form-text text-muted">Please enter the Phone Number</small>
    </div>

    <div class="form-group">
        <label for="user_id_cust">Email</label>
        <input type="text" class="form-control" name="cust_email" aria-describedby="userIdHelp" placeholder="Enter Email">
        <small id="userIdHelp" class="form-text text-muted">Please enter the Email</small>
    </div>

    <a class="btn btn-info" href="{{ url()->previous() }}">Cancel</a>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
@endsection
