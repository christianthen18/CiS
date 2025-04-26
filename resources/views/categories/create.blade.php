@extends('layouts.conquer')

@section('content') 
<form method="POST" action="{{ route('categories.store') }}">
    @csrf
    <div class="form-group">
        <label for="cust_name">Category Name</label>
        <input type="text" class="form-control" name="cat_name" aria-describedby="nameHelp" placeholder="Enter Your Category Name">
        <small id="nameHelp" class="form-text text-muted">Please enter your category name</small>
    </div>
    <a class="btn btn-info" href="{{ url()->previous() }}">Cancel</a>
    <button type="submit" class="btn btn-primary">Submit</button>
</form>
@endsection
