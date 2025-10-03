<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Check - EMIZOR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Configuration Completeness Check</h1>
        <a href="{{ route('emizor.admin.accounts') }}" class="btn btn-info mb-3">View All Accounts</a>
        <a href="{{ route('emizor.admin.config.index') }}" class="btn btn-secondary mb-3">Back to Config</a>
        <a href="{{ route('emizor.admin.invoices.index', ['account_id' => request('account_id')]) }}" class="btn btn-success mb-3">View Invoices</a>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="card">
            <div class="card-header">
                <h5>Status: <span class="badge bg-{{ $isComplete ? 'success' : 'danger' }}">{{ $isComplete ? 'Complete' : 'Incomplete' }}</span></h5>
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($checks as $check => $status)
                        @if($check !== 'parametric_details')
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ ucfirst(str_replace('_', ' ', $check)) }}
                            <span class="badge bg-{{ $status ? 'success' : 'danger' }}">
                                {{ $status ? '✓' : '✗' }}
                            </span>
                        </li>
                        @endif
                    @endforeach
                </ul>

                @if(isset($checks['parametric_details']))
                <h6 class="mt-3">Parametric Details:</h6>
                <ul class="list-group mt-2">
                    @foreach($checks['parametric_details'] as $type => $has)
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ ucfirst(str_replace('-', ' ', $type)) }}
                            <div>
                                <span class="badge bg-{{ $has ? 'success' : 'danger' }}">
                                    {{ $has ? '✓' : '✗' }}
                                </span>
                                @if(!$has)
                                    <form method="post" action="{{ route('emizor.admin.config.sync', $type) }}" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="account_id" value="{{ request('account_id') ?: 'some-uuid' }}">
                                        <button type="submit" class="btn btn-sm btn-warning ms-2">Sync</button>
                                    </form>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                @endif

                @if($isComplete)
                    <div class="alert alert-success mt-3">
                        <strong>Great!</strong> Your EMIZOR configuration is complete and ready to use.
                    </div>
                @else
                    <div class="alert alert-warning mt-3">
                        <strong>Warning:</strong> Some configuration items are missing. Please complete them to ensure proper functionality.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>