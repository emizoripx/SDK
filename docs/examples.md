# Code Examples

This document provides practical code examples for using the EMIZOR SDK.

## Account Registration

### Register a New Account

```php
use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\Facade\EmizorSdk;

// Using facade
$accountId = EmizorSdk::register([
    'clientId' => 'your_client_id',
    'clientSecret' => 'your_client_secret',
    'host' => 'PILOTO',
    'demo' => true
]);

// Using DTO
$dto = new RegisterDTO('client_id', 'client_secret', 'PILOTO', true);
$accountId = $api->register($dto);

echo "Account registered with ID: $accountId";
```

## Parametric Management

### Sync Parametrics

```php
// Sync multiple parametric types
$api->syncParametrics([
    'actividades',
    'productos',
    'metodos-de-pago',
    'tipos-documento-identidad',
    'tipos-documento-sector',
    'tipos-emision',
    'tipos-habitacion',
    'tipos-moneda',
    'unidades-medida'
]);

echo "Parametrics synced successfully";
```

### Get Parametric Data

```php
// Get activities
$activities = $api->getParametric('actividades');
foreach ($activities as $activity) {
    echo "Code: {$activity['codigo']}, Description: {$activity['descripcion']}\n";
}

// Get payment methods
$paymentMethods = $api->getParametric('metodos-de-pago');
```

### List Available Parametric Types

```php
$types = $api->listParametricTypes();
print_r($types);
```

## Default Configuration

### Set Account Defaults

```php
$api->setDefaults(function ($builder) {
    $builder->setActivityCode('620100')
            ->setBranch([
                'codigo' => '001',
                'descripcion' => 'Sucursal Principal'
            ])
            ->setCurrency('BOB')
            ->setDocumentSector('1')
            ->setEmissionType('1');
});

echo "Defaults configured";
```

### Get Current Defaults

```php
$defaults = $api->getDefaults();
print_r($defaults);
```

## Product Homologation

### Homologate Products

```php
$products = [
    [
        'codigoProducto' => 'PROD001',
        'descripcionProducto' => 'Producto de ejemplo',
        'codigoActividad' => '620100',
        'codigoProductoSin' => '83111'
    ]
];

$api->homologateProduct($products);
echo "Products homologated";
```

### List Homologated Products

```php
$homologated = $api->homologateProductList();
foreach ($homologated as $product) {
    echo "Code: {$product['codigoProducto']}, SIN: {$product['codigoProductoSin']}\n";
}
```

## Invoice Management

### Issue an Invoice

```php
$ticket = 'INV-' . time();

$api->issueInvoice(function ($builder) use ($ticket) {
    $builder->setClient([
                'nombreRazonSocial' => 'Cliente Ejemplo S.A.',
                'codigoTipoDocumentoIdentidad' => '1',
                'numeroDocumento' => '123456789',
                'complemento' => '',
                'codigoCliente' => 'CLI001'
            ])
            ->setInvoiceDetails([
                'numeroFactura' => 1,
                'codigoSucursal' => '001',
                'codigoPuntoVenta' => '001',
                'fechaEmision' => now()->format('Y-m-d\TH:i:s.v'),
                'codigoTipoDocumentoIdentidad' => '1',
                'numeroDocumento' => '123456789',
                'complemento' => '',
                'codigoCliente' => 'CLI001',
                'codigoMetodoPago' => '1',
                'numeroTarjeta' => null,
                'codigoMoneda' => 'BOB',
                'tipoCambio' => 1,
                'montoTotal' => 100.00,
                'montoTotalMoneda' => 100.00,
                'usuario' => 'admin',
                'codigoDocumentoSector' => '1'
            ])
            ->addItem([
                'codigoProducto' => 'PROD001',
                'descripcion' => 'Producto de ejemplo',
                'codigoProductoSin' => '83111',
                'codigoActividad' => '620100',
                'codigoUnidadMedida' => '1',
                'cantidad' => 1,
                'precioUnitario' => 100.00,
                'montoDescuento' => 0,
                'subtotal' => 100.00
            ])
            ->setPaymentMethod('efectivo');
}, $ticket);

echo "Invoice issued with ticket: $ticket";
```

### Revocate an Invoice

```php
$api->revocateInvoice('INV-1234567890', 1); // 1 = Error en el sistema
echo "Invoice revocated";
```

## NIT Validation

### Validate Taxpayer ID

```php
$result = $api->validateNit('123456789');
if ($result['valid']) {
    echo "NIT is valid for: {$result['razonSocial']}";
} else {
    echo "NIT is invalid";
}
```

## Complete Workflow Example

```php
<?php

namespace App\Http\Controllers;

use Emizor\SDK\Contracts\EmizorApiContract;
use Emizor\SDK\DTO\RegisterDTO;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    private EmizorApiContract $api;

    public function __construct(EmizorApiContract $api)
    {
        $this->api = $api;
    }

    public function registerAccount(Request $request)
    {
        try {
            $dto = new RegisterDTO(
                $request->client_id,
                $request->client_secret,
                $request->host,
                $request->demo
            );

            $accountId = $this->api->register($dto);

            return response()->json(['account_id' => $accountId]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function setupAccount(Request $request, $accountId)
    {
        try {
            // Get account-specific API instance
            $accountApi = app('emizorsdk', ['accountId' => $accountId]);

            // Sync parametrics
            $accountApi->syncParametrics(['actividades', 'productos', 'metodos-de-pago']);

            // Set defaults
            $accountApi->setDefaults(function ($builder) {
                $builder->setActivityCode('620100')
                        ->setBranch(['codigo' => '001', 'descripcion' => 'Principal']);
            });

            return response()->json(['message' => 'Account setup complete']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function createInvoice(Request $request, $accountId)
    {
        try {
            $accountApi = app('emizorsdk', ['accountId' => $accountId]);

            $ticket = 'INV-' . time();

            $accountApi->issueInvoice(function ($builder) use ($request) {
                $builder->setClient($request->client)
                        ->setInvoiceDetails($request->invoice_details);

                foreach ($request->items as $item) {
                    $builder->addItem($item);
                }

                $builder->setPaymentMethod($request->payment_method);
            }, $ticket);

            return response()->json(['ticket' => $ticket]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
```

## Event Listeners

```php
<?php

namespace App\Listeners;

use Emizor\SDK\Events\InvoiceAccepted;
use Illuminate\Support\Facades\Log;

class HandleInvoiceAccepted
{
    public function handle(InvoiceAccepted $event)
    {
        Log::info("Invoice accepted: {$event->ticket}");

        // Update local database
        // Send notifications
        // Trigger business logic
    }
}
```

## Testing Examples

```php
<?php

use Emizor\SDK\Contracts\EmizorApiContract;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;

it('registers account successfully', function () {
    $api = app(EmizorApiContract::class);

    $dto = new RegisterDTO('test_id', 'test_secret', 'PILOTO', true);

    $accountId = $api->register($dto);

    expect($accountId)->toBeString();
    expect($accountId)->not->toBeEmpty();
});

it('throws exception on invalid registration', function () {
    $api = app(EmizorApiContract::class);

    $dto = new RegisterDTO('', '', 'PILOTO', true);

    expect(fn() => $api->register($dto))->toThrow(EmizorApiRegisterException::class);
});
```</content>
</xai:function_call"> 

<xai:function_call name="todowrite">
<parameter name="todos">[{"content":"Generate usage and examples documentation files","status":"completed","priority":"high","id":"generate_usage_examples_docs"}]