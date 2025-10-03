<?php

namespace Emizor\SDK;

use Emizor\SDK\Contracts\EmizorApiContract;
use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\GetInvoiceDetailContract;
use Emizor\SDK\Contracts\HomologateProductContract;
use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Contracts\Invoice\InvoiceEmissionContract;
use Emizor\SDK\Contracts\Invoice\InvoiceManagerContract;
use Emizor\SDK\Contracts\Invoice\InvoiceRevocationContract;
use Emizor\SDK\Contracts\NitValidationContract;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Http\LaravelHttpClient;
use Emizor\SDK\Jobs\BeiTrackingOfflineInvoicesCron;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Observers\BeiAccountObserver;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Repositories\HomologateProductRepository;
use Emizor\SDK\Repositories\InvoiceRepository;
use Emizor\SDK\Repositories\ParametricRepository;
use Emizor\SDK\Services\EmizorApiService;
use Emizor\SDK\Services\HomologateProductService;
use Emizor\SDK\Services\Invoice\GetInvoiceDetailService;
use Emizor\SDK\Services\Invoice\InvoiceEmissionService;
use Emizor\SDK\Services\Invoice\InvoiceManagerService;
use Emizor\SDK\Services\Invoice\InvoiceRevocationService;
use Emizor\SDK\Services\NitValidationService;
use Emizor\SDK\Services\ParametricService;
use Emizor\SDK\Services\TokenService;
use Emizor\SDK\Validators\AccountValidator;
use Emizor\SDK\Validators\HomologateProductsValidator;
use Emizor\SDK\Validators\ParametricSyncValidator;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class EmizorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . "/Database/migrations");

        $this->loadRoutesFrom(__DIR__ . '/routes/web.php');

        $this->loadViewsFrom(__DIR__ . '/resources/views', 'emizor');

        $this->loadFactoriesFrom(__DIR__ . '/Database/factories');


        BeiAccount::observe(BeiAccountObserver::class);

        $this->app->booted(function () {
            $schedule = $this->app->make(Schedule::class);
            $schedule->job(new BeiTrackingOfflineInvoicesCron)->Hourly()->withoutOverlapping()->name('tracking-offline-invoices')->onOneServer();
        });

        $listeners = config('emizor_sdk.listeners', []);

        foreach ($listeners as $event => $eventListeners) {
            foreach ($eventListeners as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {

        $this->app->bind(GetInvoiceDetailContract::class, function ($app) {
            $http =  $app->make(EmizorApiHttpContract::class);
            return new GetInvoiceDetailService($http);
        });

        $this->app->bind(InvoiceRevocationContract::class, function ($app) {
            $http =  $app->make(EmizorApiHttpContract::class);
            return new InvoiceRevocationService($http);
        });

        $this->app->bind(NitValidationContract::class, function ($app) {
            $http =  $app->make(EmizorApiHttpContract::class);
            return new NitValidationService($http);
        });
        $this->app->bind(EmizorApiHttpContract::class, function ($app) {
            $http =  $app->make(HttpClientInterface::class);
            return new EmizorApiService($http);
        });
        $this->app->bind(TokenContract::class, function ($app) {
            $http = $app->make(EmizorApiHttpContract::class);
            return new TokenService($http);
        });
        $this->app->bind(InvoiceEmissionContract::class, function ($app) {
            $http = $app->make(EmizorApiHttpContract::class);
            return new InvoiceEmissionService($http);
        });

        $this->app->bind(EmizorApiContract::class, function ($app, $params) {
            return new EmizorApi(
                $app->make(AccountRepository::class),
                $app->make(AccountValidator::class),
                $app->make(ParametricSyncValidator::class),
                $app->make(ParametricContract::class),
                $app->make(HomologateProductContract::class),
                $app->make(InvoiceManagerContract::class),
                $app->make(NitValidationContract::class),
                $params['accountId'] ?? null // 🔹 parámetro opcional
            );
        });
        $this->app->bind(ParametricContract::class, function($app) {
            $http = $app->make(HttpClientInterface::class);
            return new ParametricService($http,  $app->make(ParametricRepository::class));
        });
        $this->app->bind(InvoiceManagerContract::class, function($app) {
            return new InvoiceManagerService( $app->make(InvoiceRepository::class), $app->make(AccountRepository::class));
        });
        $this->app->bind(HomologateProductContract::class, function($app) {
            return new HomologateProductService(
                $app->make(HomologateProductRepository::class),
                $app->make(HomologateProductsValidator::class)
            );
        });

        $this->app->bind(HttpClientInterface::class, function ($app) {
            // Puedes usar una URL de prueba aquí
            return new LaravelHttpClient();
        });
        // Registra el alias del facade
        $this->app->bind('emizorsdk', function ($app, $params) {
            return $app->make(EmizorApiContract::class, [
                'accountId' => $params['accountId'] ?? null,
            ]);
        });

        $this->mergeConfigFrom(
            __DIR__ . '/../config/emizor_sdk.php', 'emizor_sdk'
        );


    }
}
