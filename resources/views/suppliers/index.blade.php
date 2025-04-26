@extends('layouts/conquer');
@section('content')
    @if (session('status'))
        <div class="alert alert-success"> {{ session('status') }}</div>
    @endif

    <div class="row mb-3">
        <div class="col-md-6">
            <a href={{ route('suppliers.create') }} class="btn btn-success" style="background-color: #000000; color: white;"> + Supplier </a>
        </div>
        <div class="col-md-6">
            <form action="{{ route('suppliers.index') }}" method="GET" class="form-inline float-right">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Search suppliers..." value="{{ $search ?? '' }}">
                    <div class="input-group-append">
                        <button class="btn btn-primary" type="submit">Search</button>
                        @if(isset($search) && $search)
                            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Clear</a>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if(isset($search) && $search)
        <div class="alert alert-info">
            Showing results for: "{{ $search }}" - {{ count($data) }} results found
            <a href="{{ route('suppliers.index') }}" class="float-right">Clear search</a>
        </div>
    @endif

    <table class="table">
        <thead>
            <tr>
                <th>Company Name</th>
                <th>Phone Number</th>
                <th>Email</th>
                <th>Address</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            @if(count($data) > 0)
                @foreach ($data as $d)
                    <tr id="tr_{{ $d->id }}">
                        <td>{{ $d->company_name }}</td>
                        <td>{{ $d->phone_number }}</td>
                        <td>{{ $d->email }}</td>
                        <td>{{ $d->address }}</td>
                        <td>
                            <a class="btn btn-warning" href="{{ route('suppliers.edit', $d->id) }}" 
                               style="background-color: #000000; color: white;">Edit</a>
                            <form method="POST" action="{{ route('suppliers.destroy', $d->id) }}" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <input type="submit" value="delete" class="btn btn-danger"
                                    onclick="return confirm('Are you sure to delete {{ $d->id }} - {{ $d->company_name }} ? ');">
                            </form>
                        </td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="5" class="text-center">No suppliers found</td>
                </tr>
            @endif
        </tbody>
    </table>

    <div class="modal fade" id="modalEditA" tabindex="-1" role="basic" aria-hidden="true">
        <div class="modal-dialog modal-wide">
            <div class="modal-content">
                <div class="modal-body" id="modalContent">
                    {{-- You can put animated loading image here... --}}
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js')
    
@endsection