<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Details - EMIZOR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Invoice Details</h1>
        <a href="{{ route('emizor.admin.invoices.index') }}" class="btn btn-secondary mb-3">Back to List</a>
        @if($invoice->hasPdf())
            <a href="{{ $invoice->getAdditional()['pdf_url'] }}" target="_blank" class="btn btn-primary mb-3">View PDF</a>
        @else
            <form method="post" action="{{ route('emizor.admin.invoices.fetch-pdf', $invoice->id) }}" style="display:inline;">
                @csrf
                <button type="submit" class="btn btn-warning mb-3">Fetch PDF</button>
            </form>
        @endif

        <div class="card">
            <div class="card-header">
                <h5>Ticket: {{ $invoice->bei_ticket }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Basic Info</h6>
                        <p><strong>Account:</strong> {{ $invoice->bei_account ? $invoice->bei_account->bei_client_id : $invoice->bei_account_id }}</p>
                        <p><strong>Amount:</strong> {{ number_format($invoice->bei_amount_total, 2) }} BOB</p>
                        <p><strong>Status:</strong>
                            <span class="badge bg-{{ $invoice->bei_step_emission == 'complete' ? 'success' : ($invoice->bei_step_emission == 'in_progress' ? 'warning' : 'secondary') }}">
                                {{ ucfirst(str_replace('_', ' ', $invoice->bei_step_emission)) }}
                            </span>
                        </p>
                        <p><strong>Emission Date:</strong> {{ $invoice->bei_emission_date ? $invoice->bei_emission_date->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        <p><strong>CUF:</strong> {{ $invoice->bei_cuf ?: 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6>Client</h6>
                        <pre>{{ json_encode($invoice->bei_client, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <h6>Details</h6>
                        <pre>{{ json_encode($invoice->bei_details, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                    <div class="col-md-6">
                        <h6>Additional</h6>
                        <pre>{{ json_encode($invoice->bei_additional, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            </div>
        </div>

        @if($additional = $invoice->getAdditional() and isset($additional->pdf_url) and !empty($additional->pdf_url))
        <div class="card mt-4">
            <div class="card-header">
                <h5>Invoice PDF</h5>
            </div>
            <div class="card-body">
                <iframe src="{{ $additional->pdf_url }}" width="100%" height="600px" style="border: none;"></iframe>
            </div>
        </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
