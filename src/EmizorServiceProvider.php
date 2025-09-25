<?php

namespace Emizor\SDK;

use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Services\ParametricService;
use Emizor\SDK\Validators\AccountValidator;
use Emizor\SDK\Validators\ParametricSyncValidator;
use Illuminate\Support\ServiceProvider;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Observers\BeiAccountObserver;
use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Services\TokenService;
use Emizor\SDK\Contracts\EmizorApiContract;
use Emizor\SDK\EmizorApi;
use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Http\LaravelHttpClient;
use Emizor\SDK\Facade\EmizorSdk;

class EmizorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publica migrations para que el usuario pueda ejecutarlas en su proyecto
        $this->loadMigrationsFrom(__DIR__ . "/Database/migrations");



        // Publica factories para tests
        $this->loadFactoriesFrom(__DIR__ . '/Database/factories');


        BeiAccount::observe(BeiAccountObserver::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TokenContract::class, function ($app) {
            $http = $app->make(HttpClientInterface::class);
            return new TokenService($http);
        });

        $this->app->bind(EmizorApiContract::class, function ($app, $params) {
            return new EmizorApi(
                $app->make(HttpClientInterface::class),
                $app->make(AccountRepository::class),
                $app->make(TokenService::class),
                $app->make(AccountValidator::class),
                $app->make(ParametricSyncValidator::class),
                $params['accountId'] ?? null // 🔹 parámetro opcional
            );
        });
        $this->app->bind(ParametricContract::class, ParametricService::class);

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


    }
}
