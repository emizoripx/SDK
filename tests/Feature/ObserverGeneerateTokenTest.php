<?php

use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Models\BeiAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('calls token service and saves token when account is created', function () {
// 1. Simula el servicio usando el contenedor de servicios de Laravel.
    $this->mock(TokenContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')
            ->once()
            ->with( 'CLIENT_ID', 'SECRET')
            ->andReturn([
                'token' => 'fake-token-del-mock',
                'expires_at' => now()->addHour(),
            ]);
        $mock->shouldReceive("setHost")->once();
    });

// 2. Actúa (crea una cuenta, lo que dispara el observador)
    $account = BeiAccount::factory()->create([
        'bei_host' => 'https://api.emizor.com',
        'bei_client_id' => 'CLIENT_ID',
        'bei_client_secret' => 'SECRET',
    ]);

// 3. Afirma (verifica los resultados)
    $this->assertDatabaseHas('bei_accounts', [
        'id' => $account->id,
        'bei_token' => 'fake-token-del-mock',
    ]);
});
