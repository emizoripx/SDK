<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices - EMIZOR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Invoices</h1>
        @if($account)
            <h2>For Account: {{ $account->id }} ({{ $account->bei_client_id }})</h2>
            <a href="{{ route('emizor.admin.accounts') }}" class="btn btn-secondary mb-3">Back to Accounts</a>
        @endif

        <!-- Filtros -->
        <form method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-3">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-select">
                        <option value="">All</option>
                        <option value="none" {{ request('status') == 'none' ? 'selected' : '' }}>None</option>
                        <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="complete" {{ request('status') == 'complete' ? 'selected' : '' }}>Complete</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="date_from" class="form-label">From Date</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3">
                    <label for="date_to" class="form-label">To Date</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">Filter</button>
                </div>
            </div>
        </form>

        <!-- Tabla -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Ticket</th>
                        <th>Account</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Emission Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $invoice)
                        <tr>
                            <td>{{ $invoice->bei_ticket }}</td>
                            <td>{{ $invoice->bei_account ? $invoice->bei_account->bei_client_id : $invoice->bei_account_id }}</td>
                            <td>{{ number_format($invoice->bei_amount_total, 2) }} BOB</td>
                            <td>
                                <span class="badge bg-{{ $invoice->bei_step_emission == 'complete' ? 'success' : ($invoice->bei_step_emission == 'in_progress' ? 'warning' : 'secondary') }}">
                                    {{ ucfirst(str_replace('_', ' ', $invoice->bei_step_emission)) }}
                                </span>
                            </td>
                            <td>{{ $invoice->bei_emission_date ? $invoice->bei_emission_date->format('Y-m-d H:i') : 'N/A' }}</td>
                             <td>
                                 <a href="{{ route('emizor.admin.invoices.show', $invoice->id) }}" class="btn btn-sm btn-info">View</a>
                                 @if($additional = $invoice->getAdditional() and isset($additional->pdf_url) and !empty($additional->pdf_url))
                                     <a href="{{ $additional->pdf_url }}" target="_blank" class="btn btn-sm btn-success">PDF</a>
                                 @endif
                             </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Paginación -->
        {{ $invoices->appends(request()->query())->links() }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>