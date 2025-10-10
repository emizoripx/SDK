<?php

use Emizor\SDK\Models\BeiAccount;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock TokenContract to prevent token generation
    $this->mock(\Emizor\SDK\Contracts\TokenContract::class, function ($mock) {
        $mock->shouldReceive('generate')->andReturn([
            'token' => 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJ0ZXN0Iiwic3ViIjoiMSIsImV4cCI6MTY5NjIzOTAyMn0.fake',
            'expires_at' => now()->addHour(),
        ]);
    });

    // Mock HttpClientInterface to prevent real HTTP calls
    $this->mock(\Emizor\SDK\Contracts\HttpClientInterface::class, function ($mock) {
        $mock->shouldReceive('post')->andReturn(['access_token' => 'fake-token', 'expires_in' => 3600]);
        $mock->shouldReceive('get')->andReturn([]);
        $mock->shouldReceive('delete')->andReturn([]);
        $mock->shouldReceive('withBaseUri')->andReturnSelf();
        $mock->shouldReceive('withToken')->andReturnSelf();
    });
});

it('registers account via command with required parameters', function () {
    $this->artisan('emizor:register', [
        '--client-id' => 'test-client-123',
        '--client-secret' => 'test-secret-456',
        '--environment' => 'piloto'
    ])
    ->expectsOutput('🚀 EMIZOR Account Registration')
    ->expectsOutput('📝 Registering account...')
    ->expectsOutput('✅ Account registered successfully!')
    ->expectsOutputToContain('Account ID:')
    ->assertExitCode(0);

    // Verify account was created
    $this->assertDatabaseHas('bei_accounts', [
        'bei_client_id' => 'test-client-123',
        'bei_host' => 'PILOTO',
    ]);
});

it('handles interactive mode', function () {
    $this->artisan('emizor:register --interactive')
        ->expectsQuestion('Client ID', 'interactive-client')
        ->expectsQuestion('Client Secret', 'interactive-secret')
        ->expectsQuestion('Environment (piloto/production) [piloto]', 'production')
        ->expectsQuestion('Owner Type (optional)', '')
        ->expectsQuestion('Owner ID (optional)', '')
        ->expectsOutput('✅ Account registered successfully!')
        ->assertExitCode(0);

    // Verify account was created with production environment
    $this->assertDatabaseHas('bei_accounts', [
        'bei_client_id' => 'interactive-client',
        'bei_host' => 'PRODUCTION',
    ]);
});

it('validates required parameters', function () {
    $this->artisan('emizor:register')
        ->expectsOutput('❌ Client ID is required')
        ->assertExitCode(1);
});

it('prevents duplicate client ids without force flag', function () {
    // Create existing account
    BeiAccount::create([
        'id' => 'test-uuid',
        'bei_client_id' => 'existing-client',
        'bei_client_secret' => 'existing-secret',
        'bei_host' => 'PILOTO',
    ]);

    $this->artisan('emizor:register', [
        '--client-id' => 'existing-client',
        '--client-secret' => 'new-secret',
    ])
    ->expectsOutput('❌ Account with this Client ID already exists!')
    ->assertExitCode(1);
});

it('allows duplicate client ids with force flag', function () {
    // Create existing account
    BeiAccount::create([
        'id' => 'test-uuid',
        'bei_client_id' => 'existing-client',
        'bei_client_secret' => 'existing-secret',
        'bei_host' => 'PILOTO',
    ]);

    $this->artisan('emizor:register', [
        '--client-id' => 'existing-client',
        '--client-secret' => 'new-secret',
        '--force' => true,
    ])
    ->expectsOutput('✅ Account registered successfully!')
    ->assertExitCode(0);

    // Should have 2 accounts with same client_id
    expect(BeiAccount::where('bei_client_id', 'existing-client')->count())->toBe(2);
});

it('validates environment parameter', function () {
    $this->artisan('emizor:register', [
        '--client-id' => 'test-client',
        '--client-secret' => 'test-secret',
        '--environment' => 'invalid-env'
    ])
    ->expectsOutput('❌ Environment must be either "piloto" or "production"')
    ->assertExitCode(1);
});

it('handles owner association', function () {
    $this->artisan('emizor:register', [
        '--client-id' => 'owner-client',
        '--client-secret' => 'owner-secret',
        '--owner-type' => 'Tests\Models\Company',
        '--owner-id' => 'test-company-id'
    ])
    ->expectsOutput('✅ Account registered successfully!')
    ->assertExitCode(0);

    // Verify owner association
    $this->assertDatabaseHas('bei_accounts', [
        'bei_client_id' => 'owner-client',
        'owner_type' => 'Tests\Models\Company',
        'owner_id' => 'test-company-id',
    ]);
});

it('validates owner model exists', function () {
    $this->artisan('emizor:register', [
        '--client-id' => 'test-client',
        '--client-secret' => 'test-secret',
        '--owner-type' => 'NonExistent\Model',
        '--owner-id' => 'test-id'
    ])
    ->expectsOutput('❌ Owner model class \'NonExistent\Model\' does not exist')
    ->assertExitCode(1);
});

it('requires owner id when owner type is specified', function () {
    $this->artisan('emizor:register', [
        '--client-id' => 'test-client',
        '--client-secret' => 'test-secret',
        '--owner-type' => 'Tests\Models\Company'
    ])
    ->expectsOutput('❌ Owner ID is required when Owner Type is specified')
    ->assertExitCode(1);
});