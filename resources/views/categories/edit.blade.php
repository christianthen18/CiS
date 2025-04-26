@extends('layouts/conquer');

@section('content') 
<form method="POST" action="{{ route('categories.update', $data->id) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">Category Name</label>
        <input type="text" class="form-control" name="name" aria-describedby="typeHelp" placeholder="Enter categories name" value="{{ $data -> name }}">
    </div>
    <a class ="btn btn-info" href="{{ url()->previous() }}"> Cancel </a>
    <button type="submit" class="btn btn-primary">Submit</button>

    
</form>
@endsection