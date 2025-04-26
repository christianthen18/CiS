@extends('layouts/conquer')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4>Edit Company Information</h4>
                </div>

                <div class="card-body">
                    @if (session('status'))
                        <div class="alert alert-success" role="alert">
                            {{ session('status') }}
                        </div>
                    @endif

                    <form action="{{ route('companies.update', $company->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row mb-3">
                            <label for="comp_name" class="col-md-4 col-form-label text-md-end">Company Name</label>
                            <div class="col-md-6">
                                <input id="comp_name" type="text" class="form-control @error('comp_name') is-invalid @enderror" name="comp_name" value="{{ old('comp_name', $company->name) }}" required>
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
                                <input id="comp_phone" type="text" class="form-control @error('comp_phone') is-invalid @enderror" name="comp_phone" value="{{ old('comp_phone', $company->phone_number) }}" required>
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
                                <textarea id="comp_address" class="form-control @error('comp_address') is-invalid @enderror" name="comp_address" required>{{ old('comp_address', $company->address) }}</textarea>
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
                                <input id="comp_email" type="email" class="form-control @error('comp_email') is-invalid @enderror" name="comp_email" value="{{ old('comp_email', $company->email) }}" required>
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
                                <input id="comp_logo" type="text" class="form-control @error('comp_logo') is-invalid @enderror" name="comp_logo" value="{{ old('comp_logo', $company->logo) }}">
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
                                    Update Company
                                </button>
                                <a href="{{ route('companies.index') }}" class="btn btn-secondary">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection