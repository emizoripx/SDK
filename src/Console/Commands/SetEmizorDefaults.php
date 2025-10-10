<?php

namespace Emizor\SDK\Console\Commands;

use Illuminate\Console\Command;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Facades\EmizorSdk;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Repositories\ParametricRepository;

class SetEmizorDefaults extends Command
{
    protected $signature = 'emizor:set-defaults
                            {--account-id= : Account UUID}
                            {--client-id= : Client ID (alternative to account-id)}
                            {--type-document= : Document type}
                            {--branch= : Branch number (0-20)}
                            {--pos= : POS number (0-100)}
                            {--payment-method= : Payment method}
                            {--reason-revocation= : Revocation reason (1|3)}
                            {--sin-product-code= : SIN product code}
                            {--activity-code= : Activity code}
                            {--interactive : Interactive mode}
                            {--show-current : Show current defaults}
                            {--validate-only : Validate only, don\'t save}';

    protected $description = 'Configure default values for an existing EMIZOR account';

    protected ParametricRepository $parametricRepo;

    public function __construct(ParametricRepository $parametricRepo)
    {
        parent::__construct();
        $this->parametricRepo = $parametricRepo;
    }

    public function handle()
    {
        $this->info('⚙️  EMIZOR Account Defaults Configuration');
        $this->info('=========================================');

        // Find account
        $account = $this->findAccount();
        if (!$account) {
            return Command::FAILURE;
        }

        // Show current defaults if requested
        if ($this->option('show-current')) {
            $this->displayCurrentDefaults($account);

            // If only showing current and no new values provided, exit
            if (!$this->hasAnyDefaultOptions()) {
                return Command::SUCCESS;
            }
        }

        // Gather new defaults
        $defaults = $this->gatherDefaults($account);

        // Validate defaults
        $validationErrors = $this->validateDefaults($defaults, $account);
        if (!empty($validationErrors)) {
            foreach ($validationErrors as $error) {
                $this->error("❌ {$error}");
            }
            return Command::FAILURE;
        }

        // Show what will be changed
        $this->showChangesSummary($account, $defaults);

        // Confirm changes (unless validate-only)
        if ($this->option('validate-only')) {
            $this->info('✅ Validation successful! No changes were made (--validate-only mode)');
            return Command::SUCCESS;
        }

        if (!$this->confirmChanges()) {
            $this->info('❌ Operation cancelled');
            return Command::SUCCESS;
        }

        // Apply changes
        try {
            $this->applyDefaults($account, $defaults);
            $this->info('✅ Defaults updated successfully!');

            // Show updated defaults
            $account->refresh();
            $this->displayCurrentDefaults($account);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to update defaults: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function findAccount(): ?BeiAccount
    {
        $accountId = $this->option('account-id');
        $clientId = $this->option('client-id');

        if ($this->option('interactive')) {
            return $this->findAccountInteractive();
        }

        if ($accountId) {
            $account = BeiAccount::find($accountId);
            if (!$account) {
                $this->error("❌ Account not found with ID: {$accountId}");
                return null;
            }
            return $account;
        }

        if ($clientId) {
            $account = BeiAccount::where('bei_client_id', $clientId)->first();
            if (!$account) {
                $this->error("❌ Account not found with Client ID: {$clientId}");
                return null;
            }
            return $account;
        }

        $this->error('❌ Please specify --account-id, --client-id, or use --interactive mode');
        return null;
    }

    private function findAccountInteractive(): ?BeiAccount
    {
        $accounts = BeiAccount::all();

        if ($accounts->isEmpty()) {
            $this->error('❌ No EMIZOR accounts found');
            return null;
        }

        $this->info('Available accounts:');
        $options = [];
        foreach ($accounts as $index => $account) {
            $options[] = "[{$index}] {$account->id} (Client: {$account->bei_client_id})";
        }

        foreach ($options as $option) {
            $this->line($option);
        }

        $selectedIndex = $this->choice('Select account', array_keys($options), 0);

        return $accounts[$selectedIndex];
    }

    private function displayCurrentDefaults(BeiAccount $account): void
    {
        $defaults = $account->bei_defaults ?? [];

        $this->newLine();
        $this->info("Current defaults for account {$account->id}:");

        $tableData = [
            ['Type Document', $defaults['type_document'] ?? 'null'],
            ['Branch', $defaults['branch'] ?? 'null'],
            ['POS', $defaults['pos'] ?? 'null'],
            ['Payment Method', $defaults['payment_method'] ?? 'null'],
            ['Reason Revocation', $defaults['reason_revocation'] ?? 'null'],
            ['SIN Product Code', $defaults['sin_product_code'] ?? 'null'],
            ['Activity Code', $defaults['activity_code'] ?? 'null'],
        ];

        $this->table(
            ['Property', 'Value'],
            $tableData
        );
    }

    private function hasAnyDefaultOptions(): bool
    {
        return $this->option('type-document') ||
               $this->option('branch') ||
               $this->option('pos') ||
               $this->option('payment-method') ||
               $this->option('reason-revocation') ||
               $this->option('sin-product-code') ||
               $this->option('activity-code');
    }

    private function gatherDefaults(BeiAccount $account): array
    {
        if ($this->option('interactive')) {
            return $this->gatherDefaultsInteractive($account);
        }

        return [
            'type_document' => $this->option('type-document'),
            'branch' => $this->option('branch'),
            'pos' => $this->option('pos'),
            'payment_method' => $this->option('payment-method'),
            'reason_revocation' => $this->option('reason-revocation'),
            'sin_product_code' => $this->option('sin-product-code'),
            'activity_code' => $this->option('activity-code'),
        ];
    }

    private function gatherDefaultsInteractive(BeiAccount $account): array
    {
        $this->info('Configure new defaults (press Enter to keep current value):');
        $this->newLine();

        $currentDefaults = $account->bei_defaults ?? [];

        return [
            'type_document' => $this->ask('Type Document', $currentDefaults['type_document'] ?? null),
            'branch' => $this->ask('Branch (0-20)', $currentDefaults['branch'] ?? null),
            'pos' => $this->ask('POS (0-100)', $currentDefaults['pos'] ?? null),
            'payment_method' => $this->ask('Payment Method', $currentDefaults['payment_method'] ?? null),
            'reason_revocation' => $this->ask('Reason Revocation (1|3)', $currentDefaults['reason_revocation'] ?? null),
            'sin_product_code' => $this->ask('SIN Product Code', $currentDefaults['sin_product_code'] ?? null),
            'activity_code' => $this->ask('Activity Code', $currentDefaults['activity_code'] ?? null),
        ];
    }

    private function validateDefaults(array $defaults, BeiAccount $account): array
    {
        $errors = [];

        // Validate branch
        if (!is_null($defaults['branch'])) {
            $branch = (int) $defaults['branch'];
            if ($branch < 0 || $branch > 20) {
                $errors[] = 'Branch must be between 0 and 20';
            }
        }

        // Validate POS
        if (!is_null($defaults['pos'])) {
            $pos = (int) $defaults['pos'];
            if ($pos < 0 || $pos > 100) {
                $errors[] = 'POS must be between 0 and 100';
            }
        }

        // Validate reason revocation
        if (!is_null($defaults['reason_revocation'])) {
            if (!in_array($defaults['reason_revocation'], ['1', '3'])) {
                $errors[] = 'Reason revocation must be 1 or 3';
            }
        }

        // Validate parametric values
        if (!is_null($defaults['type_document'])) {
            if (!$this->isValidParametric($account, ParametricType::TIPOS_DOCUMENTO_IDENTIDAD, $defaults['type_document'])) {
                $errors[] = "Type document '{$defaults['type_document']}' does not exist in parametrics";
            }
        }

        if (!is_null($defaults['payment_method'])) {
            if (!$this->isValidParametric($account, ParametricType::METODOS_DE_PAGO, $defaults['payment_method'])) {
                $errors[] = "Payment method '{$defaults['payment_method']}' does not exist in parametrics";
            }
        }

        if (!is_null($defaults['sin_product_code'])) {
            if (!$this->isValidParametric($account, ParametricType::PRODUCTOS_SIN, $defaults['sin_product_code'])) {
                $errors[] = "SIN product code '{$defaults['sin_product_code']}' does not exist in parametrics";
            }
        }

        if (!is_null($defaults['activity_code'])) {
            if (!$this->isValidParametric($account, ParametricType::ACTIVIDADES, $defaults['activity_code'])) {
                $errors[] = "Activity code '{$defaults['activity_code']}' does not exist in parametrics";
            }
        }

        return $errors;
    }

    private function isValidParametric(BeiAccount $account, ParametricType $type, string $value): bool
    {
        return $this->parametricRepo->hasType($type->value, $account->id) &&
               !empty($this->parametricRepo->list($type->value, $account->id, $value));
    }

    private function showChangesSummary(BeiAccount $account, array $defaults): void
    {
        $currentDefaults = $account->bei_defaults ?? [];
        $changes = [];

        foreach ($defaults as $key => $value) {
            $currentValue = $currentDefaults[$key] ?? null;
            if ($value !== null && $value !== $currentValue) {
                $changes[] = [
                    'field' => $key,
                    'from' => $currentValue ?? 'null',
                    'to' => $value
                ];
            }
        }

        if (empty($changes)) {
            $this->info('ℹ️  No changes to apply');
            return;
        }

        $this->newLine();
        $this->info('📝 Changes to be applied:');
        $this->table(
            ['Field', 'From', 'To'],
            array_map(function ($change) {
                return [
                    ucfirst(str_replace('_', ' ', $change['field'])),
                    $change['from'],
                    $change['to']
                ];
            }, $changes)
        );
    }

    private function confirmChanges(): bool
    {
        if ($this->option('interactive')) {
            return $this->confirm('Save changes?', true);
        }
        return true; // Auto-confirm in non-interactive mode
    }

    private function applyDefaults(BeiAccount $account, array $defaults): void
    {
        // Filter out null values to avoid overwriting existing defaults
        $filteredDefaults = array_filter($defaults, function ($value) {
            return $value !== null;
        });

        if (empty($filteredDefaults)) {
            return;
        }

        // Use the SDK to set defaults (this will handle validation and saving)
        $api = EmizorSdk::for($account);
        $api->setDefaults(function ($builder) use ($filteredDefaults) {
            if (isset($filteredDefaults['type_document'])) {
                $builder->setTypeDocument($filteredDefaults['type_document']);
            }
            if (isset($filteredDefaults['branch'])) {
                $builder->setBranch($filteredDefaults['branch']);
            }
            if (isset($filteredDefaults['pos'])) {
                $builder->setPos($filteredDefaults['pos']);
            }
            if (isset($filteredDefaults['payment_method'])) {
                $builder->setPaymentMethod($filteredDefaults['payment_method']);
            }
            if (isset($filteredDefaults['reason_revocation'])) {
                $builder->setReasonRevocation($filteredDefaults['reason_revocation']);
            }
            if (isset($filteredDefaults['sin_product_code'])) {
                $builder->setSinProductCode($filteredDefaults['sin_product_code']);
            }
            if (isset($filteredDefaults['activity_code'])) {
                $builder->setActivityCode($filteredDefaults['activity_code']);
            }
        });
    }
}