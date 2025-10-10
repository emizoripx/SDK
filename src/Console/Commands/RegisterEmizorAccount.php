<?php

namespace Emizor\SDK\Console\Commands;

use Illuminate\Console\Command;
use Emizor\SDK\Facades\EmizorSdk;
use Emizor\SDK\Enums\EnvironmentType;
use Emizor\SDK\Models\BeiAccount;

class RegisterEmizorAccount extends Command
{
    protected $signature = 'emizor:register
                            {--client-id= : EMIZOR Client ID}
                            {--client-secret= : EMIZOR Client Secret}
                            {--environment=piloto : Environment (piloto|production)}
                            {--owner-type= : Owner model class (optional)}
                            {--owner-id= : Owner model ID (optional)}
                            {--interactive : Interactive mode}
                            {--force : Force registration even if account exists}';

    protected $description = 'Register a new EMIZOR account';

    public function handle()
    {
        $this->info('🚀 EMIZOR Account Registration');
        $this->info('==============================');

        // Gather input data
        $data = $this->gatherInput();

        // Validate inputs
        $this->validateInputs($data);

        // Check if account already exists
        if (!$this->option('force') && $this->accountExists($data['client_id'])) {
            $this->error('❌ Account with this Client ID already exists!');
            $this->info('💡 Use --force to override or specify a different Client ID');
            return Command::FAILURE;
        }

        // Register account
        try {
            $this->info('📝 Registering account...');

            $accountId = EmizorSdk::register(function ($builder) use ($data) {
                $builder->setClientId($data['client_id'])
                        ->setClientSecret($data['client_secret']);

                if ($data['environment'] === 'production') {
                    $builder->useProductionEnvironment();
                } else {
                    $builder->usePilotoEnvironment();
                }

                if ($data['owner_type'] && $data['owner_id']) {
                    $builder->setOwnerType($data['owner_type'])
                            ->setOwnerId($data['owner_id']);
                }
            });

            $this->newLine();
            $this->info('✅ Account registered successfully!');
            $this->info("🔑 Account ID: {$accountId}");

            // Show additional information
            $this->displayAccountInfo($accountId);

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ Registration failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function gatherInput(): array
    {
        if ($this->option('interactive')) {
            return $this->gatherInteractiveInput();
        }

        return [
            'client_id' => $this->option('client-id'),
            'client_secret' => $this->option('client-secret'),
            'environment' => $this->option('environment'),
            'owner_type' => $this->option('owner-type'),
            'owner_id' => $this->option('owner-id'),
        ];
    }

    private function gatherInteractiveInput(): array
    {
        $this->info('Please provide the following information:');
        $this->newLine();

        return [
            'client_id' => $this->ask('Client ID'),
            'client_secret' => $this->secret('Client Secret'),
            'environment' => $this->choice('Environment', ['piloto', 'production'], 'piloto'),
            'owner_type' => $this->askOptional('Owner Type (optional)'),
            'owner_id' => $this->askOptional('Owner ID (optional)', null, function ($value) {
                return !empty($this->askOptional('Owner Type (optional)')) ? $value : null;
            }),
        ];
    }

    private function askOptional(string $question, $default = null, callable $condition = null): ?string
    {
        $answer = $this->ask($question, $default);

        if ($condition && !$condition($answer)) {
            return null;
        }

        return empty($answer) ? null : $answer;
    }

    private function validateInputs(array $data): void
    {
        $errors = [];

        if (empty($data['client_id'])) {
            $errors[] = 'Client ID is required';
        }

        if (empty($data['client_secret'])) {
            $errors[] = 'Client Secret is required';
        }

        if (!in_array($data['environment'], ['piloto', 'production'])) {
            $errors[] = 'Environment must be either "piloto" or "production"';
        }

        if ($data['owner_type'] && !class_exists($data['owner_type'])) {
            $errors[] = "Owner model class '{$data['owner_type']}' does not exist";
        }

        if ($data['owner_type'] && empty($data['owner_id'])) {
            $errors[] = 'Owner ID is required when Owner Type is specified';
        }

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->error("❌ {$error}");
            }
            exit(Command::FAILURE);
        }
    }

    private function accountExists(string $clientId): bool
    {
        return BeiAccount::where('bei_client_id', $clientId)->exists();
    }

    private function displayAccountInfo(string $accountId): void
    {
        $account = BeiAccount::find($accountId);

        if (!$account) {
            return;
        }

        $this->newLine();
        $this->info('📊 Account Details:');
        $this->table(
            ['Property', 'Value'],
            [
                ['Account ID', $account->id],
                ['Client ID', $account->bei_client_id],
                ['Environment', $account->bei_host],
                ['Token Generated', $account->bei_token ? '✅ Yes' : '❌ No'],
                ['Owner Type', $account->owner_type ?? 'None'],
                ['Owner ID', $account->owner_id ?? 'None'],
                ['Created At', $account->created_at->format('Y-m-d H:i:s')],
            ]
        );

        if (!$account->bei_token) {
            $this->warn('⚠️  Token not generated yet. It will be generated automatically on first API call.');
        }

        $this->newLine();
        $this->info('🎉 Next steps:');
        $this->info('   • Run parametric sync: php artisan emizor:sync-parametrics');
        $this->info('   • Set account defaults: Configure via API or admin panel');
        $this->info('   • Start issuing invoices!');
    }
}