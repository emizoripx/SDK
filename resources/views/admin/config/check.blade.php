<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMIZOR Configuration Check</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>EMIZOR Configuration Check</h1>

    <div class="row">
        <div class="col-md-6">
            <h2>Status</h2>
            <ul class="list-group">
                @foreach($checks as $key => $value)
                    @if(is_array($value))
                        <li class="list-group-item">
                            <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                            <ul>
                                @foreach($value as $subKey => $subValue)
                                    <li>{{ ucfirst(str_replace('-', ' ', $subKey)) }}: <span class="badge badge-{{ $subValue ? 'success' : 'danger' }}">{{ $subValue ? 'Yes' : 'No' }}</span></li>
                                @endforeach
                            </ul>
                        </li>
                    @else
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            {{ ucfirst(str_replace('_', ' ', $key)) }}
                            <span class="badge badge-{{ $value ? 'success' : 'danger' }}">{{ $value ? 'Yes' : 'No' }}</span>
                        </li>
                    @endif
                @endforeach
            </ul>
        </div>
        <div class="col-md-6">
            <h2>Overall Status</h2>
            @if($isComplete)
                <div class="alert alert-success">
                    <h4>Configuration Complete!</h4>
                    <p>All checks passed. Your EMIZOR setup is ready.</p>
                </div>
            @else
                <div class="alert alert-warning">
                    <h4>Configuration Incomplete</h4>
                    <p>Some checks failed. Please review and complete the setup.</p>
                </div>
            @endif
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>