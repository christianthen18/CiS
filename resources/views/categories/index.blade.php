@extends('layouts/conquer');
@section('content')
    @if (session('status'))
        <div class="alert alert-success"> {{ session('status') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <a href={{ route('categories.create') }} class="btn btn-success" style="background-color: #000000; color: white;"> + Category </a>
        </div>
        <div class="col-md-6">
            <form action="{{ route('categories.index') }}" method="GET" class="form-inline float-right">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search by name" value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Search</button>
                        @if(isset($search) && $search)
                            <a href="{{ route('categories.index') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($search) && $search)
        <div class="alert alert-info">
            Showing results for: "{{ $search }}" - {{ count($data) }} results found
            <a href="{{ route('categories.index') }}" class="float-right">Clear search</a>
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Category Name</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @if(count($data) > 0)
                @foreach ($data as $d)
                    <tr id="tr_{{ $d->id }}">
                        <td>{{ $d->name }}</td>
                        <td>
                            <a class="btn btn-warning" href="{{ route('categories.edit', $d->id) }}"
                                style="background-color: #000000; color: white;"> Edit</a>
                            <form method="POST" action="{{ route('categories.destroy', $d->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="Delete" class="btn btn-danger"
                                    onclick="return confirm('Are you sure to delete {{ $d->id }} - {{ $d->name }} ? ');">
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="2" class="text-center">No categories found</td>
                </tr>
            @endif
        </tbody>
    </table>
@endsection