<?php

use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Models\BeiAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

it('calls token service and saves token when account is created', function () {
    // 1. Mock the service using Laravel's service container.
    $this->mock(TokenContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')
            ->once()
            ->with('https://api.emizor.com', 'CLIENT_ID', 'SECRET')
            ->andReturn([
                'token' => 'fake-mock-token',
                'expires_at' => now()->addHour(),
            ]);

    });

    // 2. Act: create an account, which triggers the observer
    $account = BeiAccount::factory()->create([
        'bei_host' => 'https://api.emizor.com',
        'bei_client_id' => 'CLIENT_ID',
        'bei_client_secret' => 'SECRET',
    ]);

    // 3. Assert: verify the results
    $this->assertDatabaseHas('bei_accounts', [
        'id' => $account->id,
        'bei_token' => 'fake-mock-token',
    ]);
});
