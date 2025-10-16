<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EMIZOR COnfiguración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container">
    <h1>Configuración de la cuenta {{ $account->id }}</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="row">
        <div class="col-md-4">
            <h2>Valores por defecto</h2>
            <form action="{{ route('emizor.admin.accounts.set-defaults', $account->id) }}" method="POST">
                @csrf
                <div class="form-group">
                    <label for="type_document">Type Document</label>
                    <select class="form-control" id="type_document" name="type_document">
                        <option value="">Select</option>
                        @foreach($globalParametrics['tipos-documento-de-identidad'] ?? [] as $item)
                            <option value="{{ $item['bei_code'] }}" {{ ($account->bei_defaults['type_document'] ?? '') == $item['bei_code'] ? 'selected' : '' }}>{{ $item['bei_description'] }}</option>
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
                            <option value="{{ $item['bei_code'] }}" {{ ($account->bei_defaults['payment_method'] ?? '') == $item['bei_code'] ? 'selected' : '' }}>{{ $item['bei_description'] }}</option>
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
                            <option value="{{ $item['bei_code'] }}" {{ ($account->bei_defaults['sin_product_code'] ?? '') == $item['bei_code'] ? 'selected' : '' }}>{{ $item['bei_description'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label for="activity_code">Activity Code</label>
                    <select class="form-control" id="activity_code" name="activity_code">
                        <option value="">Select</option>
                        @foreach($specificParametrics['actividades'] ?? [] as $item)
                            <option value="{{ $item['bei_code'] }}" {{ ($account->bei_defaults['activity_code'] ?? '') == $item['bei_code'] ? 'selected' : '' }}>{{ $item['bei_description'] }}</option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Actualizar</button>
            </form>
        </div>

        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Paramétricas específicas</h5>
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
                <div class="col-md-12">
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5>Paramétricas globales</h5>
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

        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

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
