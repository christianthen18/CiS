@extends('layouts.conquer')

@section('content')
    <div class="container mt-5">
        <h2 class="text-center mb-4">Purchase Return Details - Invoice Number: {{ $retur->invoice_number }}</h2>

        <div class="card shadow-sm">
            <div class="card-body">
                <h5><strong>Product:</strong> {{ $retur->product->name }}</h5>
                <h5><strong>Return Quantity:</strong> {{ $retur->quantity }}</h5>
                <h5><strong>Refund Amount:</strong> Rp {{ number_format($retur->refund_amount, 0, ',', '.') }}</h5>
                <h5><strong>Status:</strong> <span id="status">{{ $retur->status }}</span></h5>
                <h5><strong>Return Description:</strong> {{ $retur->retur_desc }}</h5>
                <h5><strong>Created At:</strong> {{ $retur->created_at }}</h5>
                <h5><strong>Updated At:</strong> {{ $retur->updated_at }}</h5>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="statusCheckbox"
                        {{ $retur->status == 'Return completed' ? 'checked' : '' }}>
                    <label class="form-check-label" for="statusCheckbox"
                        style="font-weight: bold; color: #fff; background-color: #28a745; padding: 5px; border-radius: 2px; margin-left: 5px;">
                        Return Completed
                    </label>
                </div>


                <div class="text-center mt-4">
                    <a href="{{ route('purchaseRetur.index') }}" class="btn btn-info">Back to Returns</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('statusCheckbox').addEventListener('change', function() {
            const isChecked = this.checked;
            const status = isChecked ? 'Return completed' : 'Return initiated';
            const returId = {{ $retur->id }};

            // Send AJAX request to update the status
            fetch(`/retur/${returId}/update-status`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        status: status
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('status').innerText = status; // Update the displayed status
                    } else {
                        alert('Failed to update status.');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
@endsection
