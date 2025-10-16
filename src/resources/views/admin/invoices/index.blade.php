<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMIZOR Invoices</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>EMIZOR Invoices</h1>

    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-3">
                <label for="status">Status</label>
                <input type="text" name="status" class="form-control" value="{{ request('status') }}">
            </div>
            <div class="col-md-3">
                <label for="date_from">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-3">
                <label for="date_to">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-3">
                <label for="account_id">Account ID</label>
                <input type="text" name="account_id" class="form-control" value="{{ request('account_id') }}">
            </div>
        </div>
        <button type="submit" class="btn btn-primary mt-2">Filter</button>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ticket</th>
                <th>Status</th>
                <th>Emission Date</th>
                <th>Account</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoices as $invoice)
                <tr>
                    <td>{{ $invoice->id }}</td>
                    <td>{{ $invoice->getTicket() }}</td>
                    <td>{{ $invoice->bei_step_emission }}</td>
                    <td>{{ $invoice->bei_emission_date }}</td>
                    <td>{{ $invoice->bei_account->bei_client_id }}</td>
                    <td>
                        <a href="{{ route('emizor.admin.invoices.show', $invoice->id) }}" class="btn btn-sm btn-info">View</a>
                        @if($invoice->bei_pdf_url)
                            <a href="{{ $invoice->bei_pdf_url }}" target="_blank" class="btn btn-sm btn-secondary">PDF</a>
                        @else
                            <form action="{{ route('emizor.admin.invoices.fetch-pdf', $invoice->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-warning">Fetch PDF</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{ $invoices->links() }}
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>