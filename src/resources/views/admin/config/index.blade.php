<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration - EMIZOR Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>EMIZOR Configuration</h1>
        <a href="{{ route('emizor.admin.accounts') }}" class="btn btn-info mb-3">View All Accounts</a>
        <a href="{{ route('emizor.admin.config.check') }}" class="btn btn-primary mb-3">Check Completeness</a>
        <a href="{{ route('emizor.admin.invoices.index', ['account_id' => request('account_id')]) }}" class="btn btn-success mb-3">View Invoices</a>

        @if(isset($error))
            <div class="alert alert-danger">{{ $error }}</div>
        @else
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Account Details</h5>
                        </div>
                        <div class="card-body">
                            <p><strong>ID:</strong> {{ $account->id }}</p>
                            <p><strong>Client ID:</strong> {{ $account->bei_client_id }}</p>
                            <p><strong>Host:</strong> {{ $account->bei_host }}</p>
                            <p><strong>Enabled:</strong>
                                <span class="badge bg-{{ $account->bei_enable ? 'success' : 'danger' }}">
                                    {{ $account->bei_enable ? 'Yes' : 'No' }}
                                </span>
                            </p>
                            <p><strong>Demo:</strong> {{ $account->bei_demo ? 'Yes' : 'No' }}</p>
                            <p><strong>Token Present:</strong>
                                <span class="badge bg-{{ $account->bei_token ? 'success' : 'danger' }}">
                                    {{ $account->bei_token ? 'Yes' : 'No' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5>Defaults</h5>
                        </div>
                        <div class="card-body">
                            @if($account->bei_defaults)
                                <pre>{{ json_encode($account->bei_defaults, JSON_PRETTY_PRINT) }}</pre>
                            @else
                                <p class="text-muted">No defaults set.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Global Parametrics</h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="globalTabs" role="tablist">
                                @foreach($globalTypes as $index => $type)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="global-{{ $type }}-tab" data-bs-toggle="tab" data-bs-target="#global-{{ $type }}" type="button" role="tab" aria-controls="global-{{ $type }}" aria-selected="{{ $index == 0 ? 'true' : 'false' }}">
                                            {{ ucfirst(str_replace('-', ' ', $type)) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content" id="globalTabsContent">
                                @foreach($globalTypes as $index => $type)
                                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="global-{{ $type }}" role="tabpanel" aria-labelledby="global-{{ $type }}-tab">
                                        <input type="text" class="form-control mb-3" placeholder="Buscar..." onkeyup="filterList(this, 'global-list-{{ $type }}')">
                                        @if(count($globalParametrics[$type]) > 0)
                                            <div id="global-list-{{ $type }}" style="max-height: 300px; overflow-y: auto;">
                                                <ul class="list-group">
                                                    @foreach($globalParametrics[$type] as $parametric)
                                                        <li class="list-group-item">
                                                            {{ $parametric['bei_code'] }} - {{ $parametric['bei_description'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <p class="text-muted">No {{ $type }} synced.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Specific Parametrics</h5>
                        </div>
                        <div class="card-body">
                            <ul class="nav nav-tabs" id="specificTabs" role="tablist">
                                @foreach($specificTypes as $index => $type)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link {{ $index == 0 ? 'active' : '' }}" id="specific-{{ $type }}-tab" data-bs-toggle="tab" data-bs-target="#specific-{{ $type }}" type="button" role="tab" aria-controls="specific-{{ $type }}" aria-selected="{{ $index == 0 ? 'true' : 'false' }}">
                                            {{ ucfirst(str_replace('-', ' ', $type)) }}
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                            <div class="tab-content" id="specificTabsContent">
                                @foreach($specificTypes as $index => $type)
                                    <div class="tab-pane fade {{ $index == 0 ? 'show active' : '' }}" id="specific-{{ $type }}" role="tabpanel" aria-labelledby="specific-{{ $type }}-tab">
                                        <input type="text" class="form-control mb-3" placeholder="Buscar..." onkeyup="filterList(this, 'specific-list-{{ $type }}')">
                                        @if(count($specificParametrics[$type]) > 0)
                                            <div id="specific-list-{{ $type }}" style="max-height: 300px; overflow-y: auto;">
                                                <ul class="list-group">
                                                    @foreach($specificParametrics[$type] as $parametric)
                                                        <li class="list-group-item">
                                                            {{ $parametric['bei_code'] }} - {{ $parametric['bei_description'] }}
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @else
                                            <p class="text-muted">No {{ $type }} synced.</p>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function filterList(input, listId) {
            const filter = input.value.toLowerCase();
            const list = document.getElementById(listId);
            const items = list.getElementsByTagName('li');
            for (let i = 0; i < items.length; i++) {
                const text = items[i].textContent || items[i].innerText;
                if (text.toLowerCase().indexOf(filter) > -1) {
                    items[i].style.display = '';
                } else {
                    items[i].style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>