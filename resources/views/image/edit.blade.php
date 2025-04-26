@extends('layouts/conquer');

@section('content') 
<form method="POST" action="{{ route('image.update', $data->id) }}">
    @csrf
    @method('PUT')
    <div class="form-group">
        <label for="name">Image Name</label>
        <input type="text" class="form-control" name="name" aria-describedby="typeHelp" placeholder="Enter image's name" value="{{ $data -> name }}">
    </div>
    <a class ="btn btn-info" href="{{ url()->previous() }}"> Cancel </a>
    <button type="submit" class="btn btn-primary">Submit</button>

    
</form>
@endsection