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

it('sets defaults via command with account id', function () {
    $account = BeiAccount::factory()->create([
        'bei_defaults' => ['type_document' => 'old_value']
    ]);

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--type-document' => '1',
        '--branch' => '1',
        '--sin-product-code' => '61191'
    ])
    ->expectsOutput('⚙️  EMIZOR Account Defaults Configuration')
    ->expectsOutput('📝 Changes to be applied:')
    ->expectsOutput('✅ Defaults updated successfully!')
    ->assertExitCode(0);

    // Verify defaults were updated
    $account->refresh();
    expect($account->bei_defaults)->toHaveKey('type_document', '1');
    expect($account->bei_defaults)->toHaveKey('branch', '1');
    expect($account->bei_defaults)->toHaveKey('sin_product_code', '61191');
});

it('finds account by client id', function () {
    $account = BeiAccount::factory()->create([
        'bei_client_id' => 'test-client-123'
    ]);

    $this->artisan('emizor:set-defaults', [
        '--client-id' => 'test-client-123',
        '--type-document' => '1'
    ])
    ->expectsOutput('✅ Defaults updated successfully!')
    ->assertExitCode(0);

    $account->refresh();
    expect($account->bei_defaults)->toHaveKey('type_document', '1');
});

it('shows current defaults with show-current flag', function () {
    $account = BeiAccount::factory()->create([
        'bei_defaults' => [
            'type_document' => '1',
            'branch' => '1'
        ]
    ]);

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--show-current' => true
    ])
    ->expectsOutput('Current defaults for account')
    ->expectsTable(['Property', 'Value'], [
        ['Type Document', '1'],
        ['Branch', '1'],
        ['POS', 'null'],
        ['Payment Method', 'null'],
        ['Reason Revocation', 'null'],
        ['SIN Product Code', 'null'],
        ['Activity Code', 'null'],
    ])
    ->assertExitCode(0);
});

it('validates branch range', function () {
    $account = BeiAccount::factory()->create();

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--branch' => '25'
    ])
    ->expectsOutput('❌ Branch must be between 0 and 20')
    ->assertExitCode(1);
});

it('validates pos range', function () {
    $account = BeiAccount::factory()->create();

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--pos' => '150'
    ])
    ->expectsOutput('❌ POS must be between 0 and 100')
    ->assertExitCode(1);
});

it('validates reason revocation values', function () {
    $account = BeiAccount::factory()->create();

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--reason-revocation' => '2'
    ])
    ->expectsOutput('❌ Reason revocation must be 1 or 3')
    ->assertExitCode(1);
});

it('handles account not found', function () {
    $this->artisan('emizor:set-defaults', [
        '--account-id' => 'non-existent-uuid',
        '--type-document' => '1'
    ])
    ->expectsOutput('❌ Account not found with ID: non-existent-uuid')
    ->assertExitCode(1);
});

it('handles client id not found', function () {
    $this->artisan('emizor:set-defaults', [
        '--client-id' => 'non-existent-client',
        '--type-document' => '1'
    ])
    ->expectsOutput('❌ Account not found with Client ID: non-existent-client')
    ->assertExitCode(1);
});

it('requires account identification', function () {
    $this->artisan('emizor:set-defaults', [
        '--type-document' => '1'
    ])
    ->expectsOutput('❌ Please specify --account-id, --client-id, or use --interactive mode')
    ->assertExitCode(1);
});

it('shows changes summary before applying', function () {
    $account = BeiAccount::factory()->create([
        'bei_defaults' => ['type_document' => 'old']
    ]);

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--type-document' => 'new',
        '--branch' => '1'
    ])
    ->expectsOutput('📝 Changes to be applied:')
    ->expectsTable(['Field', 'From', 'To'], [
        ['Type Document', 'old', 'new'],
        ['Branch', 'null', '1'],
    ])
    ->assertExitCode(0);
});

it('supports validate-only mode', function () {
    $account = BeiAccount::factory()->create();

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--type-document' => '1',
        '--validate-only' => true
    ])
    ->expectsOutput('✅ Validation successful! No changes were made (--validate-only mode)')
    ->assertExitCode(0);

    // Verify no changes were made
    $account->refresh();
    expect($account->bei_defaults)->toBeNull();
});

it('handles empty defaults gracefully', function () {
    $account = BeiAccount::factory()->create();

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id
    ])
    ->expectsOutput('ℹ️  No changes to apply')
    ->assertExitCode(0);
});

it('preserves existing defaults when setting partial values', function () {
    $account = BeiAccount::factory()->create([
        'bei_defaults' => [
            'type_document' => 'existing',
            'branch' => '5'
        ]
    ]);

    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--pos' => '10'
    ])
    ->expectsOutput('✅ Defaults updated successfully!')
    ->assertExitCode(0);

    $account->refresh();
    expect($account->bei_defaults)->toHaveKey('type_document', 'existing');
    expect($account->bei_defaults)->toHaveKey('branch', '5');
    expect($account->bei_defaults)->toHaveKey('pos', '10');
});

it('handles interactive mode when no accounts exist', function () {
    // Ensure no accounts exist
    BeiAccount::query()->delete();

    $this->artisan('emizor:set-defaults --interactive')
        ->expectsOutput('❌ No EMIZOR accounts found')
        ->assertExitCode(1);
});

it('filters null values when applying defaults', function () {
    $account = BeiAccount::factory()->create();

    // Set some defaults, leave others null
    $this->artisan('emizor:set-defaults', [
        '--account-id' => $account->id,
        '--type-document' => '1',
        '--branch' => '',  // Empty string should be treated as null
        '--pos' => '5'
    ])
    ->expectsOutput('✅ Defaults updated successfully!')
    ->assertExitCode(0);

    $account->refresh();
    expect($account->bei_defaults)->toHaveKey('type_document', '1');
    expect($account->bei_defaults)->toHaveKey('pos', '5');
    expect($account->bei_defaults)->not->toHaveKey('branch'); // Should not be set
});