@extends('layouts/conquer')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Company Information</h4>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if(count($data) > 0)
                        <!-- Display existing company information -->
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Name</th>
                                        <th>Phone</th>
                                        <th>Address</th>
                                        <th>Email</th>
                                        <th>Logo</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($data as $company)
                                    <tr>
                                        <td>{{ $company->id }}</td>
                                        <td>{{ $company->name }}</td>
                                        <td>{{ $company->phone_number }}</td>
                                        <td>{{ $company->address }}</td>
                                        <td>{{ $company->email }}</td>
                                        <td>
                                            @if($company->logo)
                                                <img src="{{ asset($company->logo) }}" alt="Company Logo" width="50">
                                            @else
                                                No Logo
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('companies.edit', $company->id) }}" class="btn btn-primary btn-sm">Edit</a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="alert alert-info mt-3">
                            <strong>Note:</strong> A company is already registered. You can only update the existing company information.
                        </div>
                    @else
                        <!-- No companies exist, show form to add a new one -->
                        <div class="alert alert-warning">
                            <strong>No company registered yet!</strong> Please add your company information below.
                        </div>
                        
                        <form action="{{ route('companies.store') }}" method="POST" class="mt-4">
                            @csrf
                            <div class="row mb-3">
                                <label for="comp_name" class="col-md-4 col-form-label text-md-end">Company Name</label>
                                <div class="col-md-6">
                                    <input id="comp_name" type="text" class="form-control @error('comp_name') is-invalid @enderror" name="comp_name" value="{{ old('comp_name') }}" required>
                                    @error('comp_name')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="comp_phone" class="col-md-4 col-form-label text-md-end">Phone Number</label>
                                <div class="col-md-6">
                                    <input id="comp_phone" type="text" class="form-control @error('comp_phone') is-invalid @enderror" name="comp_phone" value="{{ old('comp_phone') }}" required>
                                    @error('comp_phone')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="comp_address" class="col-md-4 col-form-label text-md-end">Address</label>
                                <div class="col-md-6">
                                    <textarea id="comp_address" class="form-control @error('comp_address') is-invalid @enderror" name="comp_address" required>{{ old('comp_address') }}</textarea>
                                    @error('comp_address')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="comp_email" class="col-md-4 col-form-label text-md-end">Email</label>
                                <div class="col-md-6">
                                    <input id="comp_email" type="email" class="form-control @error('comp_email') is-invalid @enderror" name="comp_email" value="{{ old('comp_email') }}" required>
                                    @error('comp_email')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <label for="comp_logo" class="col-md-4 col-form-label text-md-end">Logo URL</label>
                                <div class="col-md-6">
                                    <input id="comp_logo" type="text" class="form-control @error('comp_logo') is-invalid @enderror" name="comp_logo" value="{{ old('comp_logo') }}">
                                    @error('comp_logo')
                                        <span class="invalid-feedback" role="alert">
                                            <strong>{{ $message }}</strong>
                                        </span>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-0">
                                <div class="col-md-6 offset-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        Register Company
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
            
            <div class="mt-3">
                <a href="{{ url('/') }}" class="btn btn-secondary">Back to Home</a>
            </div>
        </div>
    </div>
</div>
@endsection