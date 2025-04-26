@extends('layouts.conquer')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-4">Warehouses Configuration</h2>
        <form action="{{ route('warehouse.updateConfiguration') }}" method="POST" id="configForm">
            @csrf
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="form-group">
                        <label for="shippings" class="font-weight-bold">Select Warehouses:</label><br>
                        @foreach ($warehouses as $warehouse)
                            <div class="form-check">
                                <input class="form-check-input config-checkbox" type="checkbox" name="warehouses[]"
                                    value="{{ $warehouse->id }}" id="warehouse{{ $warehouse->id }}"
                                    {{ in_array($warehouse->id, old('warehouses', [])) || $warehouse->statusActive == 1 ? 'checked' : '' }}
                                    data-original-state="{{ $warehouse->statusActive == 1 ? '1' : '0' }}"
                                    data-config-type="warehouse" data-config-name="{{ $warehouse->name }}"
                                    onclick="trackConfigChange(this)"
                                    {{ $warehouse->types === 'mandatory' ? 'disabled' : '' }}>
                                <label class="form-check-label" for="warehouse{{ $warehouse->id }}">
                                    {{ $warehouse->name }}
                                </label>
                                <div>
                                    <small class="text-muted">{{ $warehouse->desc }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-primary" onclick="showConfirmation()">Save
                            Configurations</button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Custom Modal Dialog -->
    <div id="customModal"
        style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; overflow: auto;">
        <div
            style="background-color: white; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0;">Confirmation</h4>
                <span onclick="hideConfirmation()"
                    style="cursor: pointer; font-size: 20px; font-weight: bold;">&times;</span>
            </div>
            <div id="modalContent" style="margin-bottom: 20px;">
                <!-- Messages will be displayed here -->
            </div>
            <div style="text-align: right;">
                <button onclick="hideConfirmation()"
                    style="padding: 5px 10px; margin-right: 10px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;">Cancel</button>
                <button onclick="submitForm()"
                    style="padding: 5px 10px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 3px; cursor: pointer;">Yes, Continue</button>
            </div>
        </div>
    </div>

    <script>
        // Store changed configs here
        var changedConfigs = [];

        // Track configuration changes
        function trackConfigChange(checkbox) {
            var originalState = checkbox.getAttribute('data-original-state') === '1';
            var currentState = checkbox.checked;
            var configName = checkbox.getAttribute('data-config-name');
            var configType = checkbox.getAttribute('data-config-type');
            var configId = checkbox.value;

            // Check if this config is already in our changed list
            var existingIndex = -1;
            for (var i = 0; i < changedConfigs.length; i++) {
                if (changedConfigs[i].id === configId && changedConfigs[i].type === configType) {
                    existingIndex = i;
                    break;
                }
            }

            // If the current state matches the original state, remove from changed list
            if (currentState === originalState) {
                if (existingIndex !== -1) {
                    changedConfigs.splice(existingIndex, 1);
                }
            } else {
                // Otherwise add or update in the changed list
                var configData = {
                    id: configId,
                    name: configName,
                    type: configType,
                    newState: currentState
                };

                if (existingIndex !== -1) {
                    changedConfigs[existingIndex] = configData;
                } else {
                    changedConfigs.push(configData);
                }
            }
        }

        // Show confirmation modal
        function showConfirmation() {
            if (changedConfigs.length === 0) {
                // No changes, submit form directly
                document.getElementById('configForm').submit();
                return;
            }

            var modalContent = document.getElementById('modalContent');
            var messageHtml = '<ul>';

            for (var i = 0; i < changedConfigs.length; i++) {
                var config = changedConfigs[i];
                var message = config.newState ?
                    "Are you sure you want to activate this " + config.type + " " + config.name + " configurations?" :
                    "Are you sure you want to disable this " + config.type + " " + config.name + " configurations?";
                messageHtml += '<li>' + message + '</li>';
            }

            messageHtml += '</ul>';
            modalContent.innerHTML = messageHtml;

            // Show the modal
            document.getElementById('customModal').style.display = 'block';
        }

        // Hide confirmation modal
        function hideConfirmation() {
            document.getElementById('customModal').style.display = 'none';
        }

        // Submit the form after confirmation
        function submitForm() {
            hideConfirmation();
            document.getElementById('configForm').submit();
        }

        // Initialize all checkboxes to track their original state
        window.onload = function() {
            var checkboxes = document.querySelectorAll('.config-checkbox');
            for (var i = 0; i < checkboxes.length; i++) {
                var checkbox = checkboxes[i];
                // Store original state directly as a boolean for easier comparison
                checkbox.setAttribute('data-original-state', checkbox.checked ? '1' : '0');
            }
        };
    </script>
@endsection
