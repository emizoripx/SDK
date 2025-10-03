<?php

use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\EmizorApi;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;
use Emizor\SDK\Exceptions\RegisterValidationException;
use Emizor\SDK\Models\BeiAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;

uses(RefreshDatabase::class);

// Define a shared setup function for tests
beforeEach(function () {
    // Always mock TokenContract to avoid dependency injection errors
    // when the observer is triggered. This applies to all tests.
    $this->mock(TokenContract::class, function (MockInterface $mock) {
        $mock->shouldReceive('generate')->andReturn([
            'token' => 'fake-mock-token',
            'expires_at' => now()->addHour(),
        ]);
        $mock->shouldReceive("setHost")->zeroOrMoreTimes();
    });

    // Resolve EmizorApi through the container
    // so all dependencies are injected correctly.
    $this->api = app(EmizorApi::class);
});

it('registers a new account successfully', function () {
    // Arrange & Act
    $dto = new RegisterDTO(
        host: 'https://api.test.com',
        clientId: 'CLIENT_001',
        clientSecret: 'SECRET_001'
    );
    $accountId = $this->api->register($dto);

    // Assert
    expect($accountId)->not()->toBeEmpty();
    $this->assertDatabaseHas('bei_accounts', [
        'id' => $accountId,
        'bei_client_id' => 'CLIENT_001',
        'bei_host' => 'https://api.test.com',
        'bei_token' => 'fake-mock-token',
    ]);
});

it('throws exception if client_id already exists', function () {
    // Arrange: create an account with duplicate client_id
    BeiAccount::factory()->create([
        'bei_client_id' => 'DUPLICATE_CLIENT',
    ]);

    $dto = new RegisterDTO(
        host: 'https://api.test.com',
        clientId: 'DUPLICATE_CLIENT',
        clientSecret: 'SECRET_002'
    );

    // Act & Assert: expect the correct exception
    $this->api->register($dto);
})->throws(EmizorApiRegisterException::class);

