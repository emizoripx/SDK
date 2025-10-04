# Code Examples

This document provides practical code examples for using the EMIZOR SDK.

## Account Registration

### Register a New Account

```php
use Emizor\SDK\Facade\EmizorSdk;

// Using facade with fluent builder
$accountId = EmizorSdk::register(function ($builder) {
    $builder->setClientId('your_client_id')
            ->setClientSecret('your_client_secret')
            ->usePilotoEnvironment(); // or useProductionEnvironment()
});

// Note: Upon successful registration, the system automatically:
// - Generates and stores an access token
// - Synchronizes global parametrics (payment methods, document types, etc.)
// - Synchronizes account-specific parametrics (activities, SIN products, legends)

// Get API instance for account operations
$api = EmizorSdk::withAccount($accountId);

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
$api->setDefaults(function ($data) {
    $data->setTypeDocument('compra-venta')
            ->setBranch('001')
            ->setPos('001')
            ->setPaymentMethod('efectivo')
            ->setReasonRevocation('1')
            ->setSinProductCode('61191')  // Must exist in synced productos-sin parametrics
            ->setActivityCode('461091');  // Must exist in synced actividades parametrics
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
use Emizor\SDK\DTO\ClientDTO;

$accountId = "your-account-id"; // From registration
$api = EmizorSdk::withAccount($accountId);

$ticket = 'INV-' . time();

$client = new ClientDTO(
    'CLI001',
    '123456789',
    'Cliente Ejemplo S.A.',
    '',
    '1',
    'cliente@example.com'
);

$details = [
    [
        'product_code' => 'PROD001',
        'description' => 'Producto de ejemplo',
        'quantity' => 1,
        'unit_price' => 100.00,
        'unit_code' => '1',
    ]
];

$api->issueInvoice(function ($builder) use ($client, $details) {
    $builder->setClient($client)
            ->setDetails($details)
            ->setTypeDocument('1')
            ->setBranch(1)
            ->setPos(1)
            ->setPaymentMethod('1')
            ->setAmount(100.00);
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
if ($result['status'] === 'success' && isset($result['data'])) {
    $data = $result['data'];
    if ($data['codigo'] == 0) {
        echo "NIT is valid for: {$data['descripcion']}";
    } else {
        echo "NIT validation failed: {$data['descripcion']}";
    }
} else {
    echo "NIT validation error";
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
            // Registration automatically handles token generation and parametric synchronization
            $accountId = $this->api->register(function ($builder) use ($request) {
                $builder->setClientId($request->client_id)
                        ->setClientSecret($request->client_secret)
                        ->usePilotoEnvironment(); // or useProductionEnvironment()
            });

            return response()->json(['account_id' => $accountId]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function setupAccount(Request $request, $accountId)
    {
        try {
            // Get account-specific API instance
            $accountApi = EmizorSdk::withAccount($accountId);

            // Sync parametrics
            $accountApi->sync(['actividades', 'productos', 'metodos-de-pago']);

            // Set defaults
            $accountApi->setDefaults(function ($data) {
                $data->setTypeDocument('compra-venta')
                        ->setBranch('001')
                        ->setPos('001');
            });

            return response()->json(['message' => 'Account setup complete']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function createInvoice(Request $request, $accountId)
    {
        try {
            $accountApi = EmizorSdk::withAccount($accountId);

            $ticket = 'INV-' . time();

            $client = new ClientDTO(
                $request->client['client_code'],
                $request->client['client_document_number'],
                $request->client['client_business_name'],
                $request->client['client_complement'],
                $request->client['client_document_number_type'],
                $request->client['client_email'] ?? null
            );

            $accountApi->issueInvoice(function ($builder) use ($client, $request) {
                $builder->setClient($client)
                        ->setDetails($request->items)
                        ->setTypeDocument($request->type_document ?? '1')
                        ->setBranch($request->branch ?? 1)
                        ->setPos($request->pos ?? 1)
                        ->setPaymentMethod($request->payment_method)
                        ->setAmount($request->amount);
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

    // Registration triggers automatic token and parametrics sync
    $accountId = $api->register(function ($builder) {
        $builder->setClientId('test_id')
                ->setClientSecret('test_secret')
                ->usePilotoEnvironment();
    });

    expect($accountId)->toBeString();
    expect($accountId)->not->toBeEmpty();
});

it('throws exception on invalid registration', function () {
    $api = app(EmizorApiContract::class);

    expect(fn() => $api->register(function ($builder) {
        $builder->setClientId('')
                ->setClientSecret('')
                ->usePilotoEnvironment();
    }))->toThrow(EmizorApiRegisterException::class);
});
```</content>
</xai:function_call"> 

<xai:function_call name="todowrite">
<parameter name="todos">[{"content":"Generate usage and examples documentation files","status":"completed","priority":"high","id":"generate_usage_examples_docs"}]