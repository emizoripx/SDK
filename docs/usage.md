# Usage Guide

This guide provides detailed instructions on how to use the EMIZOR SDK in your Laravel application.

## Installation

1. Install the package via Composer:

```bash
composer require emizor/sdk
```

2. Publish the configuration file (optional, for customizing event listeners):

```bash
php artisan vendor:publish --provider="Emizor\SDK\EmizorServiceProvider"
```

3. Run the migrations to create necessary database tables:

```bash
php artisan migrate
```

## Configuration

### Environment Variables

Add the following to your `.env` file (if not using the SDK for account registration):

```env
EMIZOR_CLIENT_ID=your_client_id
EMIZOR_CLIENT_SECRET=your_client_secret
EMIZOR_HOST=PILOTO  # or PRODUCTION
EMIZOR_DEMO=true    # set to false for production
```

### Config File

After publishing, edit `config/emizor_sdk.php` to configure owners and event listeners:

```php
'owners' => [
    'company' => App\Models\Company::class,
    'user' => App\Models\User::class,
    // Add your owner models here
],

'listeners' => [
    \Emizor\SDK\Events\InvoiceAccepted::class => [
        \App\Listeners\HandleInvoiceAccepted::class,
    ],
    \Emizor\SDK\Events\InvoiceRejected::class => [
        \App\Listeners\HandleInvoiceRejected::class,
    ],
],
```

## Basic Usage

### Using the Facade

```php
use Emizor\SDK\Facade\EmizorSdk;

// Register a new account tied to an owner (e.g., Company model)
$accountId = EmizorSdk::register(function ($builder) use ($company) {
    $builder->setClientId(env('EMIZOR_CLIENT_ID'))
            ->setClientSecret(env('EMIZOR_CLIENT_SECRET'))
            ->setOwnerType(get_class($company))
            ->setOwnerId($company->id)
            ->usePilotoEnvironment(); // or useProductionEnvironment()
});

// Note: Registration automatically triggers:
// - Token generation and storage
// - Synchronization of global parametrics
// - Synchronization of account-specific parametrics

// Use the dynamic manager for operations
$api = EmizorSdk::for($company);

// Most operations now require valid token and synced parametrics
try {
    $api->syncParametrics(['actividades']);
} catch (\Exception $e) {
    // Handle token or parametric validation errors
    Log::error('Operation failed: ' . $e->getMessage());
}
```

### Using Dependency Injection

```php
use Emizor\SDK\Contracts\EmizorApiContract;

class InvoiceController extends Controller
{
    private EmizorApiContract $api;

    public function __construct(EmizorApiContract $api)
    {
        $this->api = $api;
    }

    public function createInvoice(Request $request)
    {
        // Use $this->api here
    }
}
```

## Workflow

1. **Register Account**: If you don't have an account ID yet, register first.
2. **Sync Parametrics**: Sync fiscal parametrics (activities, products, etc.) for offline use.
3. **Set Defaults**: Configure default values for your account using the new `useCompraVentaDocumentType()` method.
4. **Homologate Products**: Register your products with fiscal codes (required for invoice issuance).
5. **Issue Invoices**: Create and emit electronic invoices using homologated product codes.
6. **Validate NIT**: Check taxpayer identification numbers.
7. **Revocate Invoices**: Cancel invoices if needed.

**Important**: Steps 2-7 require a valid token and synced parametrics. The SDK automatically validates this before operations.

## Error Handling

The SDK throws specific exceptions that all extend `EmizorSdkException`:

- `EmizorApiRegisterException` (code 1004): Registration errors
- `EmizorApiTokenException` (code 1005): Token-related errors
- `EmizorApiConnectionTimeoutException` (code 1002): Connection timeouts
- `EmizorApiAccountException` (code 1001): Account-related errors
- `EmizorApiDefaultsValidationException` (code 1003): Defaults validation errors
- `EmizorApiSyncException` (code 1008): Sync operation errors
- `EmizorApiDefaultsException` (code 1009): Defaults operation errors
- `EmizorApiAssociationException` (code 1010): Product association errors
- `ParametricSyncValidationException` (code 1006): Invalid parametric data
- `RegisterValidationException` (code 1007): Invalid registration data

**New validation errors:**
- Operations may throw generic `Exception` with messages like "No token found, please refresh token before continue." or "No parametrics found, please complete parametrics sync." when token or parametric checks fail.

You can catch the base `EmizorSdkException` to handle all SDK errors generically, or catch `Exception` for validation errors.

Always wrap API calls in try-catch blocks:

```php
try {
    $api->syncParametrics(['actividades']);
    $api->setDefaults(function ($builder) {
        $builder->useCompraVentaDocumentType()
                ->setActivityCode('461091');
    });
} catch (\Emizor\SDK\Exceptions\EmizorSdkException $e) {
    // Handle SDK-specific errors
    Log::error('SDK error: ' . $e->getMessage());
} catch (\Exception $e) {
    // Handle validation errors (token, parametrics)
    Log::error('Validation error: ' . $e->getMessage());
}
```

## Events

Listen to invoice events in your application:

```php
// In a service provider or event listener
\Event::listen(\Emizor\SDK\Events\InvoiceAccepted::class, function ($event) {
    // $event->ticket, $event->status, $event->meta
    Log::info("Invoice {$event->ticket} accepted");
});
```

## Testing

Run the test suite:

```bash
composer test
```

Or with Docker:

```bash
docker-compose exec app composer test
```

## Troubleshooting

- Ensure your EMIZOR account credentials are correct
- Check that the host (PILOTO/PRODUCTION) matches your account
- Verify database migrations have been run
- Check logs for detailed error messages</content>
</xai:function_call"> 

<xai:function_call name="write">
<parameter name="filePath">/home/marcus/proyects/fel-api-cli/docs/examples.md