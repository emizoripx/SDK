<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMIZOR Accounts</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Listado de cuentas</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-8">

            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Client ID</th>
                        <th>Environment</th>
                        <th>Token</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($accounts as $account)
                        <tr>
                            <td>{{ $account->id }}</td>
                            <td>{{ $account->bei_client_id }}</td>
                            <td>{{ $account->bei_host }}</td>
                            <td>{{ $account->bei_token ? 'Yes' : 'No' }}</td>
                            <td>
                                <a href="{{ route('emizor.admin.config.index', ['account_id' => $account->id]) }}" class="btn btn-sm btn-primary">Configure</a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="col-md-4">
            <h2>Register New Account</h2>
            <form action="{{ route('emizor.admin.accounts.register') }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="client_id">Client ID</label>
                    <input type="text" class="form-control" id="client_id" name="client_id" required>
                </div>
                <div class="form-group">
                    <label for="client_secret">Client Secret</label>
                    <input type="password" class="form-control" id="client_secret" name="client_secret" required>
                </div>
                <div class="form-group">
                    <label for="environment">Environment</label>
                    <select class="form-control" id="environment" name="environment" required>
                        <option value="piloto">Piloto</option>
                        <option value="production">Production</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="owner_type">Owner Type </label>
                    <input type="text" class="form-control" id="owner_type" name="owner_type">
                </div>
                <div class="form-group">
                    <label for="owner_id">Owner ID </label>
                    <input type="text" class="form-control" id="owner_id" name="owner_id">
                </div>
                <button type="submit" class="btn btn-success">Registrar</button>
            </form>
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
