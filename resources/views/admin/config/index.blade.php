<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMIZOR Configuration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>EMIZOR Configuration for Account {{ $account->id }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-6">
            <h2>Account Defaults</h2>
            <form action="{{ route('emizor.admin.accounts.set-defaults', $account->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="type_document">Type Document</label>
                    <select class="form-control" id="type_document" name="type_document">
                        <option value="">Select</option>
                        @foreach($globalParametrics['tipos-documento-de-identidad'] ?? [] as $item)
                            <option value="{{ $item['codigo'] }}" {{ ($account->bei_defaults['type_document'] ?? '') == $item['codigo'] ? 'selected' : '' }}>{{ $item['descripcion'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="branch">Branch (0-20)</label>
                    <input type="number" class="form-control" id="branch" name="branch" min="0" max="20" value="{{ $account->bei_defaults['branch'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="pos">POS (0-100)</label>
                    <input type="number" class="form-control" id="pos" name="pos" min="0" max="100" value="{{ $account->bei_defaults['pos'] ?? '' }}">
                </div>
                <div class="form-group">
                    <label for="payment_method">Payment Method</label>
                    <select class="form-control" id="payment_method" name="payment_method">
                        <option value="">Select</option>
                        @foreach($globalParametrics['metodos-de-pago'] ?? [] as $item)
                            <option value="{{ $item['codigo'] }}" {{ ($account->bei_defaults['payment_method'] ?? '') == $item['codigo'] ? 'selected' : '' }}>{{ $item['descripcion'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="reason_revocation">Reason Revocation</label>
                    <select class="form-control" id="reason_revocation" name="reason_revocation">
                        <option value="">Select</option>
                        <option value="1" {{ ($account->bei_defaults['reason_revocation'] ?? '') == '1' ? 'selected' : '' }}>1</option>
                        <option value="3" {{ ($account->bei_defaults['reason_revocation'] ?? '') == '3' ? 'selected' : '' }}>3</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="sin_product_code">SIN Product Code</label>
                    <select class="form-control" id="sin_product_code" name="sin_product_code">
                        <option value="">Select</option>
                        @foreach($specificParametrics['productos-sin'] ?? [] as $item)
                            <option value="{{ $item['codigo'] }}" {{ ($account->bei_defaults['sin_product_code'] ?? '') == $item['codigo'] ? 'selected' : '' }}>{{ $item['descripcion'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="activity_code">Activity Code</label>
                    <select class="form-control" id="activity_code" name="activity_code">
                        <option value="">Select</option>
                        @foreach($specificParametrics['actividades'] ?? [] as $item)
                            <option value="{{ $item['codigo'] }}" {{ ($account->bei_defaults['activity_code'] ?? '') == $item['codigo'] ? 'selected' : '' }}>{{ $item['descripcion'] }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Defaults</button>
            </form>
        </div>

        <div class="col-md-6">
            <h2>Parametrics</h2>
            <!-- Display parametrics here -->
            @foreach($globalParametrics as $type => $items)
                <h3>{{ ucfirst(str_replace('-', ' ', $type)) }}</h3>
                <ul>
                    @foreach($items as $item)
                        <li>{{ $item['codigo'] }} - {{ $item['descripcion'] }}</li>
                    @endforeach
                </ul>
            @endforeach
            @foreach($specificParametrics as $type => $items)
                <h3>{{ ucfirst(str_replace('-', ' ', $type)) }}</h3>
                <ul>
                    @foreach($items as $item)
                        <li>{{ $item['codigo'] }} - {{ $item['descripcion'] }}</li>
                    @endforeach
                </ul>
            @endforeach
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>