<?php

namespace Emizor\SDK;

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
        $this->loadMigrationsFrom(__DIR__ . "/database/migrations");



        // Publica factories para tests
        $this->loadFactoriesFrom(__DIR__ . '/Database/factories');


        BeiAccount::observe(BeiAccountObserver::class);
    }

    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(TokenContract::class, TokenService::class);
        $this->app->bind(EmizorApiContract::class, EmizorApi::class);

        $this->app->bind(HttpClientInterface::class, function ($app) {
            // Puedes usar una URL de prueba aquí
            return new LaravelHttpClient();
        });
        // Registra el alias del facade
        $this->app->singleton('emizorsdk', function ($app) {
            return $app->make(EmizorApiContract::class);
        });


    }
}
