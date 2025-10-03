<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts - EMIZOR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>EMIZOR Accounts</h1>
        <p>Selecciona una cuenta para ver su configuración y paramétricos específicos.</p>

        @if($accounts->count() > 0)
            <div class="row">
                @foreach($accounts as $account)
                    <div class="col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title">{{ $account->id }}</h5>
                                <p class="card-text">
                                    <strong>Client ID:</strong> {{ $account->bei_client_id }}<br>
                                    <strong>Host:</strong> {{ $account->bei_host }}<br>
                                    <strong>Enabled:</strong> {{ $account->bei_enable ? 'Yes' : 'No' }}<br>
                                    <strong>Token:</strong> {{ $account->bei_token ? 'Present' : 'Missing' }}
                                </p>
                                <a href="{{ route('emizor.admin.config.index', ['account_id' => $account->id]) }}" class="btn btn-primary">View Config</a>
                                <a href="{{ route('emizor.admin.config.check', ['account_id' => $account->id]) }}" class="btn btn-secondary">Check Completeness</a>
                                <a href="{{ route('emizor.admin.invoices.index', ['account_id' => $account->id]) }}" class="btn btn-info">View Invoices</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info">No accounts found. Register an account first.</div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>