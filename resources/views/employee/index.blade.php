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
                <a href={{ route('employe.create') }} class= "btn btn-success"
                    style="background-color: #000000; color: white;"> +
                    Employee </a>
            </div>
        </div>

        <div class="row no-gutters">
            <div class="col-12 p-0">
                <table class="table table-striped table-hover m-0">
                    <thead>
                        <tr>
                            <th>Employee Name</th>
                            <th>Phone Number</th>
                            <th>Address</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            // Check if any user already has role ID 2 (Owner)
                            $ownerExists = DB::table('users')->where('roles_id', 2)->exists();
                        @endphp
                        
                        @foreach ($data as $d)
                            <tr id="tr_{{ $d->id }}">
                                <td>{{ $d->name }}</td>
                                <td>{{ $d->phone_number }}</td>
                                <td>{{ $d->address }}</td>
                                <td>
                                    @if($d->user)
                                        {{ $d->user->username }}
                                    @else
                                        <span class="text-muted">No user linked</span>
                                    @endif
                                </td>
                                <td>
                                    @if($d->user)
                                        <form method="POST" action="{{ route('update.user.role', $d->user->id) }}" class="d-flex align-items-center">
                                            @csrf
                                            @method('PUT')
                                            <select name="role_id" class="form-control form-control-sm mr-2" {{ $d->user->roles_id == 2 && $ownerExists ? 'disabled' : '' }}>
                                                <option value="1" {{ $d->user->roles_id == 1 ? 'selected' : '' }}>Admin</option>
                                                <option value="2" {{ $d->user->roles_id == 2 ? 'selected' : '' }} 
                                                    {{ $ownerExists && $d->user->roles_id != 2 ? 'disabled' : '' }}>
                                                    Owner {{ $ownerExists && $d->user->roles_id != 2 ? '(Already assigned)' : '' }}
                                                </option>
                                            </select>
                                            <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                        </form>
                                    @else
                                        <span class="text-muted">No role assigned</span>
                                    @endif
                                </td>
                                <td class="d-flex">
                                    <a class="btn btn-warning mr-2" href="{{ route('employe.edit', $d->id) }}"
                                        style="background-color: #000000; color: white;">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('employe.destroy', $d->id) }}" class="m-0">
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