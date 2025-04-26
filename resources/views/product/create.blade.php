@extends('layouts.conquer')

@section('content')
    <form method="POST" action="{{ route('product.store') }}" id="productForm" enctype="multipart/form-data">
        @csrf

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto">Product Information</legend>

            <div class="form-group">
                <label for="product_name">Product Name</label>
                <input type="text" class="form-control" name="product_name" placeholder="Enter Your Product" required>
                <small class="form-text text-muted">Please enter your product name</small>
            </div>

            <!-- Other product information fields... -->
            <div class="form-group">
                <label for="product_desc">Description</label>
                <input type="text" class="form-control" name="product_desc" placeholder="Enter Product Description"
                    required>
            </div>

            <div class="form-group">
                <label for="product_price">Price</label>
                <input type="number" class="form-control" name="product_price" placeholder="Enter Product Price" required>
            </div>

            <div class="form-group">
                <label for="product_cost">Cost</label>
                <input type="number" class="form-control" name="product_cost" placeholder="Enter Product Cost" required>
            </div>

            <div class="form-group">
                <label for="product_stock">Stock</label>
                <input type="number" class="form-control" name="product_stock" placeholder="Enter Product Stock" required>
            </div>

            <div class="form-group">
                <label for="product_minstock">Minimum Stock</label>
                <input type="number" class="form-control" name="product_minstock" min="5"
                    placeholder="Enter Minimum Stock" required>
            </div>

            <div class="form-group">
                <label for="product_maksretur">Maximum Return</label>
                <input type="number" class="form-control" name="product_maksretur" max="5"
                    placeholder="Enter Maximum Return" required>
            </div>
        </fieldset>

        <fieldset class="border p-3 mb-4">
            <legend class="w-auto">Additional Information</legend>

            <div class="form-group">
                <label for="file_photo">Upload Product Image</label>
                <input type="file" class="form-control-file" name="file_photo" id="file_photo">
                <small class="form-text text-muted">Upload an image for this product (JPEG, PNG, JPG, GIF up to 2MB)</small>
            </div>

            {{-- <div class="form-group">
                <label for="product_image_id">Select Image</label>
                <select class="form-control" name="product_image_id">
                    <option value="" selected>Select Your Image</option>
                    <option value="">None</option> <!-- Option to unselect -->
                    @foreach ($product_image as $img)
                        <option value="{{ $img->id }}">{{ $img->name }}</option>
                    @endforeach
                </select>
                <small class="form-text text-muted">If there is no suitable product photo then select "None" or leave
                    blank!</small>
            </div> --}}



            <div class="form-group">
                <label for="product_category_id">Select Category</label>
                <select class="form-control" name="product_category_id" required>
                    <option value="" disabled selected>Select Your Category</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label for="product_supplier_id">Select Supplier</label>
                <select class="form-control" name="product_supplier_id" required>
                    <option value="" disabled selected>Select Your Supplier</option>
                    @foreach ($suppliers as $supplier)
                        <option value="{{ $supplier->id }}">{{ $supplier->company_name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- COGS Method Section -->
            <div class="form-group">
                <label for="cogs_method">Select Cogs Method:</label>
                <select class="form-control" name="cogs_method" required>
                    <option value="">Select COGS Method</option>
                    @foreach ($activeCogs as $cogs_method)
                        <option value="{{ strtolower(str_replace('P-', '', $cogs_method->name)) }}">
                            {{ $cogs_method->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Dynamic Warehouse Configuration -->
            @if (isset($warehouseOptions) && $warehouseOptions->count() > 0)
                <div class="form-group">
                    <label for="warehouse_selection">Select Warehouse Option:</label><br>

                    @php
                        $multiWarehouseOption = $warehouseOptions->where('id', 14)->first();
                        $directlyInStoreOption = $warehouseOptions->where('id', 15)->first();
                    @endphp

                    @if ($multiWarehouseOption)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="warehouse_option" id="multiWarehouse"
                                value="multi" {{ $multiWarehouseOption ? 'checked' : '' }}>
                            <label class="form-check-label" for="multiWarehouse">
                                Multi-warehouse
                            </label>
                            <div>
                                <small class="text-muted">{{ $multiWarehouseOption->desc }}</small>
                            </div>
                        </div>
                    @endif

                    @if ($directlyInStoreOption)
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="warehouse_option" id="directlyInStore"
                                value="direct" {{ !$multiWarehouseOption && $directlyInStoreOption ? 'checked' : '' }}>
                            <label class="form-check-label" for="directlyInStore">
                                Directly in store
                            </label>
                            <div>
                                <small class="text-muted">{{ $directlyInStoreOption->desc }}</small>
                            </div>
                        </div>
                    @endif
                </div>

                @if ($multiWarehouseOption)
                    <div id="warehouseDropdown" class="form-group mt-3"
                        {{ !$multiWarehouseOption ? 'style="display:none;"' : '' }}>
                        <label for="product_warehouse_id">Select Warehouse:</label>
                        <select class="form-control" name="product_warehouse_id" id="product_warehouse_id">
                            <option value="" disabled selected>Select Your Warehouse</option>
                            @foreach ($warehouses as $warehouse)
                                <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                            @endforeach
                        </select>
                        <small id="warehouseHelp" class="form-text text-muted">Please select a warehouse if
                            Multi-warehouse is selected.</small>
                    </div>
                @endif
            @else
                <div class="alert alert-warning">
                    No warehouse options are currently active. Please contact the administrator.
                </div>
            @endif
        </fieldset>

        <div class="d-flex justify-content-between">
            <a class="btn btn-info" href="{{ url()->previous() }}">Cancel</a>
            <button type="button" class="btn btn-primary" onclick="showConfirmation()">Submit</button>
        </div>
    </form>

    <!-- Custom Modal Dialog -->
    <div id="customModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; overflow: auto;">
        <div
            style="background-color: white; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0;">Konfirmasi</h4>
                <span onclick="hideConfirmation()"
                    style="cursor: pointer; font-size: 20px; font-weight: bold;">&times;</span>
            </div>
            <div id="modalContent" style="margin-bottom: 20px;">
                <!-- Messages will be displayed here -->
            </div>
            <div style="text-align: right;">
                <button onclick="hideConfirmation()"
                    style="padding: 5px 10px; margin-right: 10px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;">Batal</button>
                <button onclick="submitForm()"
                    style="padding: 5px 10px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 3px; cursor: pointer;">Ya,
                    Lanjutkan</button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const multiWarehouseRadio = document.getElementById('multiWarehouse');
            const directlyInStoreRadio = document.getElementById('directlyInStore');
            const warehouseDropdown = document.getElementById('warehouseDropdown');

            if (multiWarehouseRadio && directlyInStoreRadio && warehouseDropdown) {
                // Show/hide warehouse dropdown based on selected option  
                multiWarehouseRadio.addEventListener('change', function() {
                    warehouseDropdown.style.display = 'block';
                    const warehouseSelect = document.getElementById('product_warehouse_id');
                    if (warehouseSelect) {
                        warehouseSelect.setAttribute('required', 'required');
                    }
                });

                directlyInStoreRadio.addEventListener('change', function() {
                    warehouseDropdown.style.display = 'none';
                    const warehouseSelect = document.getElementById('product_warehouse_id');
                    if (warehouseSelect) {
                        warehouseSelect.removeAttribute('required');
                    }
                });

                // Initialize dropdown visibility  
                warehouseDropdown.style.display = multiWarehouseRadio.checked ? 'block' : 'none';
                if (multiWarehouseRadio.checked) {
                    const warehouseSelect = document.getElementById('product_warehouse_id');
                    if (warehouseSelect) {
                        warehouseSelect.setAttribute('required', 'required');
                    }
                }
            }
        });

        function showConfirmation() {
            const form = document.getElementById('productForm');

            // Validate form
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Get selected warehouse option
            const multiWarehouseRadio = document.getElementById('multiWarehouse');
            const warehouseSelect = document.getElementById('product_warehouse_id');

            // Validate warehouse selection if multi-warehouse is selected
            if (multiWarehouseRadio && multiWarehouseRadio.checked && warehouseSelect && !warehouseSelect.value) {
                alert('Please select a warehouse first!');
                return;
            }

            let message = '';

            if (multiWarehouseRadio && multiWarehouseRadio.checked && warehouseSelect) {
                const warehouseName = warehouseSelect.options[warehouseSelect.selectedIndex].text;
                message = `Apakah anda yakin ingin menyimpan produk ini di warehouse ${warehouseName}?`;
            } else {
                message = "Apakah anda yakin ingin menyimpan produk ini tanpa menambahkan ke warehouse?";
            }

            document.getElementById('modalContent').innerHTML = message;
            document.getElementById('customModal').style.display = 'block';
        }

        function hideConfirmation() {
            document.getElementById('customModal').style.display = 'none';
        }

        function submitForm() {
            hideConfirmation();
            document.getElementById('productForm').submit();
        }
    </script>
@endsection
