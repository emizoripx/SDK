# EMIZOR SDK
Issue invoices according to Bolivian fiscal regulation using EMIZOR services.

## Requirements
- Laravel ^11
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
    docker-compose exec app composer install
    docker-compose exec app composer test
  ```

## Usage

After installation, you can use the SDK via facade or dependency injection.

### Facade

```php
use Emizor\SDK\Facade\EmizorSdk;

// Register account
$accountId = EmizorSdk::register([
    'clientId' => 'your_client_id',
    'clientSecret' => 'your_client_secret',
    'host' => 'PILOTO',
    'demo' => true
]);

// Then use with account
$api = app('emizorsdk', ['accountId' => $accountId]);
$api->syncParametrics(['actividades', 'productos']);
```

### Dependency Injection

```php
use Emizor\SDK\Contracts\EmizorApiContract;

public function __construct(EmizorApiContract $api) {
    $this->api = $api;
}
```

## Examples

### Register Account

```php
use Emizor\SDK\DTO\RegisterDTO;

$accountId = $api->register(new RegisterDTO('client_id', 'client_secret', 'PILOTO', true));
```

### Sync Parametrics

```php
$api->syncParametrics(['actividades', 'metodos-de-pago']);
$parametric = $api->getParametric('actividades');
```

### Set Defaults

```php
$api->setDefaults(function ($builder) {
    $builder->setActivityCode('123')
            ->setBranch(['code' => '001', 'name' => 'Main Branch']);
});
```

### Issue Invoice

```php
$api->issueInvoice(function ($builder) {
    $builder->setClient(['name' => 'Client Name', 'nit' => '123456'])
            ->addItem(['code' => '001', 'description' => 'Product', 'quantity' => 1, 'price' => 100])
            ->setPaymentMethod('efectivo');
}, 'ticket-123');
```

### Validate NIT

```php
$result = $api->validateNit('123456');
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

- `register(RegisterDTO $dto): string` - Register a new account
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
- Register EMIZOR SDK account to get ACCOUNT_ID
- Obtain access token for API calls

### Parametric Sync
- Sync fiscal parametrics (activities, products, payment methods, etc.)
- Store locally for offline use

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
