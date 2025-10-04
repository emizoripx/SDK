<?php

use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\EmizorApi;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;
use Emizor\SDK\Exceptions\RegisterValidationException;
use Emizor\SDK\Models\BeiAccount;
use Mockery\MockInterface;

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
    $accountId = $this->api->register(function ($builder) {
        $builder->setClientId('12345678')
                ->setClientSecret('SECRET_001')
                ->usePilotoEnvironment();
    });

    // Assert
    expect($accountId)->not()->toBeEmpty();
    $this->assertDatabaseHas('bei_accounts', [
        'id' => $accountId,
        'bei_client_id' => '12345678',
        'bei_host' => 'PILOTO',
        'bei_token' => 'fake-mock-token',
    ]);
});

it('throws exception if client_id already exists', function () {
    // Arrange: create an account with duplicate client_id
    BeiAccount::factory()->create([
        'bei_client_id' => '12345678',
    ]);

    // Act & Assert: expect the correct exception
    $this->api->register(function ($builder) {
        $builder->setClientId('12345678')
                ->setClientSecret('SECRET_002')
                ->usePilotoEnvironment();
    });
})->throws(\Emizor\SDK\Exceptions\EmizorApiRegisterException::class);

