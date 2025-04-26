@extends('layouts/conquer')

@section('content')
    <div class="page-content">
        <div class="row">
            <div class="col-md-12">
                <h2 class="text-center mb-4">Product Catalog</h2>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <a class="btn btn-warning btn-sm" style="background-color: #000000; color: white;"
                        href="{{ route('product.create') }}"><i class="fa fa-plus"></i> Add New Product</a>
                    <span class="text-muted">
                        @if (request()->has('search') ||
                                request()->has('category_id') ||
                                request()->has('min_price') ||
                                request()->has('max_price') ||
                                request()->has('brand'))
                            Showing {{ $datas->count() }} results
                            <a href="{{ route('product.index') }}" class="btn btn-sm btn-outline-secondary ml-2">Clear All
                                Filters</a>
                        @else
                            Showing all {{ $datas->count() }} products
                        @endif
                    </span>
                </div>

                <!-- Search Results Indication -->
                @if (request('search') || request('brand'))
                    <div class="alert alert-info">
                        @if (request('search'))
                            Search results for: <strong>"{{ request('search') }}"</strong>
                        @endif

                        @if (request('brand'))
                            @if (request('search'))
                                and
                            @endif
                            Brand: <strong>"{{ request('brand') }}"</strong>
                        @endif

                        <a href="{{ route('product.index', array_filter(request()->except(['search', 'brand']))) }}"
                            class="float-right">Clear search</a>
                    </div>
                @endif
                
                <!-- Advanced Search Form -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#searchCollapse"
                                aria-expanded="true">
                                <i class="fa fa-search"></i> Search & Filter Products
                            </button>
                        </h5>
                    </div>
                    <div id="searchCollapse"
                        class="collapse {{ request()->hasAny(['search', 'category_id', 'min_price', 'max_price']) ? 'show' : '' }}">
                        <div class="card-body">
                            <form action="{{ route('product.index') }}" method="GET">
                                <div class="row">
                                    <!-- Keyword Search -->
                                    <div class="col-md-6 mb-3">
                                        <label for="search">Search by name or description</label>
                                        <input type="text" id="search" name="search" class="form-control"
                                            placeholder="Enter keywords..." value="{{ request('search') }}">
                                    </div>

                                    <!-- Category Filter -->
                                    <div class="col-md-3 mb-3">
                                        <label for="category_id">Category</label>
                                        <select name="category_id" id="category_id" class="form-control">
                                            <option value="">All Categories</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}"
                                                    {{ request('category_id') == $category->id ? 'selected' : '' }}>
                                                    {{ $category->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Brand Filter (New) -->
                                    <div class="col-md-3 mb-3">
                                        <label for="brand">Brand</label>
                                        <select name="brand" id="brand" class="form-control">
                                            <option value="">All Brands</option>
                                            @foreach ($brands as $brandOption)
                                                <option value="{{ $brandOption }}"
                                                    {{ request('brand') == $brandOption ? 'selected' : '' }}>
                                                    {{ $brandOption }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Price Range -->
                                    <div class="col-md-6 mb-3">
                                        <div class="row">
                                            <div class="col-6">
                                                <label for="min_price">Min Price</label>
                                                <input type="number" id="min_price" name="min_price" class="form-control"
                                                    placeholder="Min" value="{{ request('min_price') }}">
                                            </div>
                                            <div class="col-6">
                                                <label for="max_price">Max Price</label>
                                                <input type="number" id="max_price" name="max_price" class="form-control"
                                                    placeholder="Max" value="{{ request('max_price') }}">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sorting Options -->
                                    <div class="col-md-6 mb-3">
                                        <label>Sort By</label>
                                        <div class="row">
                                            <div class="col-8">
                                                <select name="sort_by" class="form-control">
                                                    <option value="name"
                                                        {{ request('sort_by') == 'name' ? 'selected' : '' }}>Name</option>
                                                    <option value="price"
                                                        {{ request('sort_by') == 'price' ? 'selected' : '' }}>Price
                                                    </option>
                                                    <option value="brands"
                                                        {{ request('sort_by') == 'brands' ? 'selected' : '' }}>Brand
                                                    </option> <!-- New sort option -->
                                                    <option value="created_at"
                                                        {{ request('sort_by') == 'created_at' ? 'selected' : '' }}>Date
                                                        Added</option>
                                                    <option value="stock"
                                                        {{ request('sort_by') == 'stock' ? 'selected' : '' }}>Stock
                                                    </option>
                                                </select>
                                            </div>
                                            <div class="col-4">
                                                <select name="sort_dir" class="form-control">
                                                    <option value="asc"
                                                        {{ request('sort_dir') == 'asc' ? 'selected' : '' }}>Ascending
                                                    </option>
                                                    <option value="desc"
                                                        {{ request('sort_dir') == 'desc' ? 'selected' : '' }}>Descending
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <a href="{{ route('product.index') }}" class="btn btn-secondary">Reset</a>
                                    <button type="submit" class="btn btn-primary">Apply Filters</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                @if (request('search'))
                    <div class="alert alert-info">
                        Search results for: <strong>"{{ request('search') }}"</strong>
                        <a href="{{ route('product.index', array_filter(request()->except('search'))) }}"
                            class="float-right">Clear search</a>
                    </div>
                @endif

                <!-- Product Grid -->
                <div class="row">
                    @if ($datas->count() > 0)
                        @foreach ($datas as $d)
                            <div class="col-md-4 mb-4">
                                <div class="card shadow-sm h-100">
                                    <!-- Product Image -->
                                    <div class="card-img-top text-center p-3">
                                        @if ($d->productImage)
                                            <img src="{{ asset('/images/' . $d->productImage->name) }}"
                                                class="img-fluid img-thumbnail" alt="{{ $d->name }}"
                                                style="max-height: 200px; object-fit: cover;">
                                        @else
                                            <img src="{{ asset('/images/no-image.png') }}"
                                                class="img-fluid img-thumbnail" alt="No image available"
                                                style="max-height: 200px; object-fit: cover;">
                                        @endif
                                    </div>
                                    <!-- Product Details -->
                                    <div class="card-body">
                                        <h5 class="card-title text-center">{{ $d->name }}</h5>

                                        <!-- Add Brand Display Here -->
                                        @if ($d->brands)
                                            <p class="card-text text-center text-primary mb-2">
                                                <strong>Brand:</strong> {{ $d->brands }}
                                            </p>
                                        @endif

                                        <p class="card-text text-muted text-center">{{ $d->desc }}</p>
                                        <p class="card-text text-center">
                                            <strong>Price:</strong> Rp {{ number_format($d->price, 0, ',', '.') }}
                                        </p>
                                        <p class="card-text text-center">
                                            <strong>Cost:</strong> Rp {{ number_format($d->cost, 0, ',', '.') }}
                                        </p>
                                        <p class="card-text text-center">
                                            <strong>Stock:</strong> {{ $d->stock }}
                                        </p>
                                        <p class="text-center text-muted">
                                            <small>Created: {{ $d->created_at }}</small><br>
                                            <small>Updated: {{ $d->updated_at }}</small>
                                        </p>
                                        <!-- Action Buttons -->
                                        <div class="text-center">
                                            <a class="btn btn-warning btn-sm"
                                                style="background-color: #000000; color: white;"
                                                href="{{ route('product.edit', $d->id) }}">Edit</a>
                                            <a class="btn btn-info btn-sm"
                                                style="background-color: #000000; color: white;"
                                                href="{{ url('product/uploadPhoto/' . $d->id) }}">Upload Photo</a>
                                            <form method="POST" action="{{ route('product.destroy', $d->id) }}"
                                                style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <input type="submit" value="Delete" class="btn btn-danger btn-sm"
                                                    onclick="return confirm('Are you sure to delete {{ $d->name }}?');">
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="col-12">
                            <div class="alert alert-warning text-center">
                                <h4><i class="fa fa-exclamation-triangle"></i> No products found</h4>
                                <p>Try adjusting your search criteria or browse all products</p>
                                <a href="{{ route('product.index') }}" class="btn btn-outline-primary">Show All
                                    Products</a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
