@extends('layouts/conquer')
@section('content')
    <div class="container-fluid p-0">
        <div class="row no-gutters align-items-center mb-0">
            <div class="col-12 d-flex justify-content-between align-items-center p-0 mb-2">
                @if (session('status'))
                    <div class="alert alert-success m-0 mr-auto">
                        {{ session('status') }}
                    </div>
                @endif
                <a href={{ route('customer.create') }} class= "btn btn-success"
                    style="background-color: #000000; color: white;"> + Customer </a>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="col-12 p-0">
                <table class="table table-striped table-hover m-0">
                    <thead>
                        <tr>
                            <th>Customers Name</th>
                            <th>Address</th>
                            <th>Phone Number</th>
                            <th>Email</th>
                            <th>Status Active</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data as $d)
                            <tr id="tr_{{ $d->id }}">
                                <td>{{ $d->name }}</td>
                                <td>{{ $d->address }}</td>
                                <td>{{ $d->phone_number }}</td>
                                <td>{{ $d->email }}</td>
                                <td>
                                    @if ($d->status_active == 1)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </td>
                                <td class="d-flex">
                                    <a class="btn btn-warning mr-2" href="{{ route('customer.edit', $d->id) }}"
                                        style="background-color: #000000; color: white;">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('customer.destroy', $d->id) }}" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <input type="submit" value="Delete" class="btn btn-danger"
                                            onclick="return confirm('Are you sure to delete {{ $d->id }} - {{ $d->name }} ? ');">
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="modalEditA" tabindex="-1" role="basic" aria-hidden="true">
            <div class="modal-dialog modal-wide">
                <div class="modal-content">
                    <div class="modal-body" id="modalContent">
                        {{-- You can put animated loading image here... --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
