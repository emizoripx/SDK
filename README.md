# EMIZOR SDK
Issue invoices according to Bolivian fiscal regulation using EMIZOR services.

## Requirements
- Laravel ^11 or ^12
- PHP ^8.2
- An account in [EMIZOR](https://emizor.com), with:
    - CLIENT_ID
    - CLIENT_SECRET
    - HOST (`PILOTO` | `PRODUCTION`)

## Installation
Using Composer in a Laravel project:
```sh
composer require emizor/sdk
```


## Test using Docker
```sh
    docker-compose up -d
    docker-compose exec php composer install
    docker-compose exec php composer test
  ```

## Usage

After installation, you can use the SDK via facade. The SDK uses an owner-based pattern where credentials are tied to your models (e.g., Company, User).

### Owner Model Setup

Add the trait to your models to enable EMIZOR credentials:

```php
<?php

namespace App\Models;

use Emizor\SDK\Traits\HasEmizorCredentials;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasEmizorCredentials;

    // ... your model code
}
```

### Facade

```php
use Emizor\SDK\Facade\EmizorSdk;

// Register account tied to an owner
$company = Company::find(1);
$accountId = EmizorSdk::register(function ($builder) use ($company) {
    $builder->setClientId('your_client_id')
            ->setClientSecret('your_client_secret')
            ->setOwnerType(get_class($company))
            ->setOwnerId($company->id)
            ->usePilotoEnvironment(); // or useProductionEnvironment()
});

// Note: Upon registration, the system automatically:
// - Generates an access token
// - Synchronizes global parametrics (payment methods, document types, etc.)
// - Synchronizes account-specific parametrics (activities, SIN products, etc.)

// Use the dynamic manager for operations
$api = EmizorSdk::for($company);
$api->syncParametrics(['actividades', 'productos']);
```

### Dependency Injection

For advanced usage, resolve the manager directly:

```php
use Emizor\SDK\Services\EmizorManager;

public function __construct(EmizorManager $api) {
    $this->api = $api;
}
```

Or use the facade for simplicity.

## Examples

### Register Account

```php
// Using fluent builder pattern, tied to an owner
$company = Company::find(1);
$accountId = EmizorSdk::register(function ($builder) use ($company) {
    $builder->setClientId('your_client_id')
            ->setClientSecret('your_client_secret')
            ->setOwnerType(get_class($company))
            ->setOwnerId($company->id)
            ->usePilotoEnvironment();
});

// Automatic post-registration process:
// 1. Token generation and storage
// 2. Global parametrics synchronization
// 3. Account-specific parametrics synchronization
```

### List parametrics available

```php
return EmizorSdk::PARAMETRICS_TYPES()
```

### Sync Parametrics

```php
$company = Company::find(1);
$api = EmizorSdk::for($company);
$api->syncParametrics(['actividades', 'metodos-de-pago']);
$parametric = $api->getParametric('actividades');
```

### Set Defaults

```php
$company = Company::find(1);
$api = EmizorSdk::for($company);
$api->setDefaults(function ($builder) {
    $builder->setActivityCode('123')
            ->setBranch(['code' => '001', 'name' => 'Main Branch']);
});
```

### Issue Invoice

```php
use Emizor\SDK\DTO\ClientDTO;

$company = Company::find(1);
$api = EmizorSdk::for($company);

$client = new ClientDTO(
    'CLI001',
    '123456789',
    'Client Name',
    '',
    '1',
    'client@example.com'
);

$api->issueInvoice(function ($builder) use ($client) {
    $builder->setClient($client)
            ->setDetails([
                [
                    'product_code' => '001',
                    'description' => 'Product',
                    'quantity' => 1,
                    'unit_price' => 100,
                    'unit_code' => '1'
                ]
            ])
            ->setPaymentMethod('efectivo')
            ->setAmount(100);
}, 'ticket-123');
```

### Validate NIT

```php
$result = $api->validateNit('123456');
if ($result['status'] === 'success' && isset($result['data'])) {
    $data = $result['data'];
    if ($data['codigo'] == 0) {
        echo "NIT is valid: {$data['descripcion']}";
    } else {
        echo "NIT validation failed: {$data['descripcion']}";
    }
}
```

### Revocate Invoice

```php
$api->revocateInvoice('ticket-123', 1); // reason code
```

## Configuration

Publish the config file:

```sh
php artisan vendor:publish --provider="Emizor\SDK\EmizorServiceProvider"
```

Configure event listeners in `config/emizor_sdk.php`:

```php
'listeners' => [
    \Emizor\SDK\Events\InvoiceAccepted::class => [
        \App\Listeners\HandleInvoiceAccepted::class,
    ],
],
```

## API Reference

### Main Methods

- `register(Closure $callback): string` - Register a new account using fluent builder
- `syncParametrics(array $parametrics): void` - Sync parametric data
- `getParametric(string $type): array` - Get synced parametric data
- `setDefaults(Closure $callback): self` - Set default configurations
- `getDefaults(): array` - Get default configurations
- `homologateProduct(array $products): void` - Homologate products
- `homologateProductList(): array` - List homologated products
- `issueInvoice(Closure $callback, string $ticket): self` - Issue an invoice
- `validateNit(string $nit): array` - Validate NIT
- `revocateInvoice(string $ticket, int $reasonCode): void` - Revocate an invoice

See source code for detailed parameters and return types.

## Events

The SDK fires the following events:

- `InvoiceAccepted` - When invoice is accepted
- `InvoiceRejected` - When invoice is rejected
- `InvoiceInProcess` - When invoice is in process
- `InvoiceRevocated` - When invoice is revocated
- `InvoiceReverted` - When invoice is reverted

## Features

### Account Register
- Register EMIZOR SDK account to get ACCOUNT_ID using fluent builder pattern
- Automatic token generation and storage upon registration
- Automatic synchronization of global and account-specific parametrics

### Parametric Sync
- Sync fiscal parametrics (activities, products, payment methods, etc.)
- Store locally for offline use
- Global parametrics synced automatically on account creation
- Account-specific parametrics synced automatically on account creation

### Invoice Management
- Issue electronic invoices
- Validate NIT
- Revocate invoices

### Product Homologation
- Homologate products with fiscal codes

## Contributing

Contributions are welcome. Please follow the changelog conventions.

## API Documentation

OpenAPI/Swagger documentation is available in `docs/swagger.yaml`.

## Admin UI (Package Included)

The package includes a basic web interface for testing and reviewing the SDK functionality:

- **Invoices**: `/emizor-admin/invoices` - List and view invoice details with filters
- **Configuration**: `/emizor-admin/config` - View account settings and synced parametrics
- **Config Check**: `/emizor-admin/config/check` - Verify configuration completeness

Routes are automatically loaded when the package is registered. Views use Bootstrap for styling. Access via `?account_id=uuid` parameter for testing different accounts.

## License

MIT

## Changelog Conventions

- All new features and hotfixes merged into the [develop] branch should be added under [Unreleased].
- The [main] branch contains only official releases.
- Format:
    - [MAJOR.MINOR.PATCH] - YYYY-MM-DD
        - Added → New implementations (APIs, classes, methods, settings).
        - Changed → Changes in business logic or behavior.
        - Deprecated → Functionalities still work but are scheduled for removal.
        - Removed → Permanently removed functionalities.
        - Fixed → Bug fixes.
        - Security → Security vulnerability fixes.
