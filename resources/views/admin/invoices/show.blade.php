@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Invoice Details</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">
            <h2>Invoice #{{ $invoice->id }}</h2>
            <table class="table">
                <tr><th>Ticket</th><td>{{ $invoice->getTicket() }}</td></tr>
                <tr><th>Status</th><td>{{ $invoice->bei_step_emission }}</td></tr>
                <tr><th>Emission Date</th><td>{{ $invoice->bei_emission_date }}</td></tr>
                <tr><th>Account</th><td>{{ $invoice->bei_account->bei_client_id }}</td></tr>
                <!-- Add more fields as needed -->
            </table>
        </div>

        <div class="col-md-4">
            @if(!$invoice->isInProgress() && !$invoice->isCompleteRevocation() && !$invoice->isInProgressRevocation())
                <h2>Revoke Invoice</h2>
                <form action="{{ route('emizor.admin.invoices.revocate', $invoice->id) }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <select class="form-control" id="reason" name="reason" required>
                            <option value="1">1 - Motivo</option>
                            <option value="3">3 - Otro</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-danger">Revoke Invoice</button>
                </form>
            @else
                <p>This invoice cannot be revoked at this time.</p>
            @endif
        </div>
    </div>
</div>
@endsection