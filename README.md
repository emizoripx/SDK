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

## Command Line Interface

### Register Account via Artisan

The package provides an Artisan command for registering EMIZOR accounts from the command line:

#### Interactive Mode
```bash
php artisan emizor:register --interactive
```

#### Non-Interactive Mode
```bash
php artisan emizor:register \
  --client-id=300455 \
  --client-secret=your-secret \
  --environment=piloto \
  --owner-type=App\\Models\\Company \
  --owner-id=company-uuid
```

#### Parameters
- `--client-id`: EMIZOR Client ID (required)
- `--client-secret`: EMIZOR Client Secret (required)
- `--environment`: `piloto` or `production` (default: `piloto`)
- `--owner-type`: Owner model class (optional)
- `--owner-id`: Owner model ID (optional)
- `--interactive`: Interactive mode
- `--force`: Force registration even if account exists

#### Examples

**Basic registration:**
```bash
php artisan emizor:register --client-id=123 --client-secret=secret
```

**Production environment:**
```bash
php artisan emizor:register \
  --client-id=123 \
  --client-secret=secret \
  --environment=production
```

**With owner association:**
```bash
php artisan emizor:register \
  --client-id=123 \
  --client-secret=secret \
  --owner-type=App\\Models\\Company \
  --owner-id=550e8400-e29b-41d4-a716-446655440000
```

**Interactive mode:**
```bash
php artisan emizor:register --interactive
# Follow the prompts to enter credentials
```

### Configure Account Defaults

Set default values for an existing EMIZOR account:

#### Direct Configuration
```bash
php artisan emizor:set-defaults \
  --account-id=550e8400-e29b-41d4-a716-446655440000 \
  --type-document=1 \
  --branch=1 \
  --pos=1 \
  --payment-method=efectivo \
  --sin-product-code=61191 \
  --activity-code=461091
```

#### View Current Defaults
```bash
php artisan emizor:set-defaults --account-id=uuid --show-current
```

#### Interactive Configuration
```bash
php artisan emizor:set-defaults --interactive
# Select account and configure defaults interactively
```

#### Parameters
- `--account-id`: Account UUID (required, unless using --client-id)
- `--client-id`: Client ID (alternative to account-id)
- `--type-document`: Document type
- `--branch`: Branch number (0-20)
- `--pos`: POS number (0-100)
- `--payment-method`: Payment method
- `--reason-revocation`: Revocation reason (1|3)
- `--sin-product-code`: SIN product code
- `--activity-code`: Activity code
- `--interactive`: Interactive mode
- `--show-current`: Show current defaults
- `--validate-only`: Validate without saving

#### Examples

**Update specific defaults:**
```bash
php artisan emizor:set-defaults --account-id=uuid --branch=2 --pos=5
```

**Find by client ID:**
```bash
php artisan emizor:set-defaults --client-id=300455 --type-document=1
```

**Validate before applying:**
```bash
php artisan emizor:set-defaults --account-id=uuid --type-document=1 --validate-only
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
