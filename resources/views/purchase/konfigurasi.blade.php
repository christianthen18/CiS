@extends('layouts.conquer')

@section('content')
    <div class="container">
        <form action="{{ route('purchase.updateConfiguration') }}" method="POST" id="configForm">
            @csrf
            {{-- SHIPPING KONFIGURATION --}}
            <h2 class="text-center mb-4">Receive Configuration</h2>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="form-group">
                        <label for="shippings">Select Receives:</label><br>
                        @foreach ($shippings as $shipping)
                            <div class="d-flex align-items-center mb-2">
                                <input class="form-check-input me-2" type="checkbox" name="shippings[]"
                                    value="{{ $shipping->id }}" id="shipping{{ $shipping->id }}"
                                    {{ $shipping->statusActive === 1 ? 'checked' : '' }}
                                    {{ $shipping->types === 'mandatory' ? 'disabled' : '' }}
                                    data-original-state="{{ $shipping->statusActive === 1 ? '1' : '0' }}"
                                    data-config-type="receive"
                                    data-config-name="{{ $shipping->name }}"
                                    onclick="trackConfigChange(this)">
                                <label class="form-check-label me-2" for="shipping{{ $shipping->id }}">
                                    {{ $shipping->name }}
                                </label>
                                <div class="input-group w-25">
                                    <span class="input-group-text">IDR</span>
                                    <input type="number" name="shipping_values[{{ $shipping->id }}]" 
                                        class="form-control" value="{{ $shipping->value }}">
                                </div>
                            </div>
                            <div>
                                <small class="text-muted">{{ $shipping->desc }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- PAYMENT METHODS KONFIGURATION --}}
            <h2 class="text-center mb-4">Payment Methods Configuration</h2>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="form-group">
                        <label for="payments">Select Payment Methods:</label><br>
                        @foreach ($payments as $payment)
                            <div class="d-flex align-items-center mb-2">
                                <input class="form-check-input me-2" type="checkbox" name="payments[]"
                                    value="{{ $payment->id }}" id="payment{{ $payment->id }}"
                                    {{ $payment->statusActive === 1 ? 'checked' : '' }}
                                    {{ $payment->types === 'mandatory' ? 'disabled' : '' }}
                                    data-original-state="{{ $payment->statusActive === 1 ? '1' : '0' }}"
                                    data-config-type="payment method"
                                    data-config-name="{{ $payment->name }}"
                                    onclick="trackConfigChange(this)">
                                <label class="form-check-label me-2" for="payment{{ $payment->id }}">
                                    {{ $payment->name }}
                                </label>
                            </div>
                            <div>
                                <small class="text-muted">{{ $payment->desc }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- COGS KONFIGURATION --}}
            <h2 class="text-center mb-4">COGS Method Configuration</h2>
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="form-group">
                        <label for="cogs">Select COGS Method:</label><br>
                        @foreach ($cogs as $cogs_method)
                            <div class="d-flex align-items-center mb-2">
                                <input class="form-check-input me-2" type="checkbox" name="cogs[]"
                                    value="{{ $cogs_method->id }}" id="cogs_method{{ $cogs_method->id }}"
                                    {{ $cogs_method->statusActive === 1 ? 'checked' : '' }}
                                    {{ $cogs_method->types === 'mandatory' ? 'disabled' : '' }}
                                    data-original-state="{{ $cogs_method->statusActive === 1 ? '1' : '0' }}"
                                    data-config-type="COGS method"
                                    data-config-name="{{ $cogs_method->name }}"
                                    onclick="trackConfigChange(this)">
                                <label class="form-check-label me-2" for="cogs_method{{ $cogs_method->id }}">
                                    {{ $cogs_method->name }}
                                </label>
                            </div>
                            <div>
                                <small class="text-muted">{{ $cogs_method->desc }}</small>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-primary" onclick="showConfirmation()">Save Configurations</button>
        </form>
        <br><br>
    </div>

    <!-- Custom Modal Dialog -->
    <div id="customModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); z-index: 1000; overflow: auto;">
        <div style="background-color: white; margin: 15% auto; padding: 20px; border: 1px solid #888; width: 50%; border-radius: 5px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h4 style="margin: 0;">Confirmation</h4>
                <span onclick="hideConfirmation()" style="cursor: pointer; font-size: 20px; font-weight: bold;">&times;</span>
            </div>
            <div id="modalContent" style="margin-bottom: 20px;">
                <!-- Messages will be displayed here -->
            </div>
            <div style="text-align: right;">
                <button onclick="hideConfirmation()" style="padding: 5px 10px; margin-right: 10px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 3px; cursor: pointer;">Cancel</button>
                <button onclick="submitForm()" style="padding: 5px 10px; background-color: #007bff; color: white; border: 1px solid #007bff; border-radius: 3px; cursor: pointer;">Yes, Continue</button>
            </div>
        </div>
    </div>

    <script>
        // Store changed configs here
        var changedConfigs = [];
        
        // Track configuration changes
        function trackConfigChange(checkbox) {
            // Skip if checkbox is disabled (mandatory)
            if (checkbox.disabled) {
                return;
            }
            
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
                    "Are you sure you want to activate this " + config.type + " " + config.name + " configuration?" :
                    "Are you sure you want to disable this " + config.type + " " + config.name + " configuration?";
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
            var checkboxes = document.querySelectorAll('input[type="checkbox"][data-config-name]');
            for (var i = 0; i < checkboxes.length; i++) {
                var checkbox = checkboxes[i];
                // Store original state directly as a boolean for easier comparison
                checkbox.setAttribute('data-original-state', checkbox.checked ? '1' : '0');
            }
        };
    </script>
@endsection