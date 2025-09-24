<?php

use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\EmizorApi;
use Emizor\SDK\Exceptions\RegisterValidationException;
use Emizor\SDK\Models\BeiAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);
// Define una función de configuración compartida para los tests
beforeEach(function () {
    // Siempre mockea el TokenContract para evitar errores de inyección de dependencias
    // cuando se dispara el observador. Esto se aplica a todos los tests.
    $this->mock(TokenContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')->andReturn([
            'token' => 'fake-token-del-mock',
            'expires_at' => now()->addHour(),
        ]);
    });

    // Resuelve la interfaz EmizorApiInterface a través del contenedor
    // para que todas las dependencias se inyecten correctamente.
    $this->api = app(EmizorApi::class);
});

it('registers a new account successfully', function () {
    // 1. Actuar
    $dto = new RegisterDTO(
        host: 'https://api.test.com',
        clientId: 'CLIENT_001',
        clientSecret: 'SECRET_001'
    );
    $accountId = $this->api->register($dto);

    // 2. Afirmar
    expect($accountId)->not()->toBeEmpty();
    $this->assertDatabaseHas('bei_accounts', [
        'id' => $accountId,
        'bei_client_id' => 'CLIENT_001',
        'bei_host' => 'https://api.test.com',
        'bei_token' => 'fake-token-del-mock',
    ]);
});

it('throws exception if client_id already exists', function () {
    // Preparar el estado: crear una cuenta con un client_id duplicado
    BeiAccount::factory()->create([
        'bei_client_id' => 'DUPLICATE_CLIENT',
    ]);

    $dto = new RegisterDTO(
        host: 'https://api.test.com',
        clientId: 'DUPLICATE_CLIENT',
        clientSecret: 'SECRET_002'
    );

    // Actuar y afirmar que se lanza la excepción correcta
    $this->api->register($dto);
})->throws(RegisterValidationException::class);

