<?php

namespace Emizor\SDK\Jobs;

use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Services\Invoice\InvoiceNitValidationService;
use Emizor\SDK\Services\TokenManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;


/**
 * Job to synchronize account-specific parametrics for a specific account.
 *
 * This job fetches and stores parametrics that are specific to the account,
 * like activities, SIN products, and legends.
 * It uses exponential backoff for retries and prevents overlapping executions.
 */
class SyncSpecificParametrics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Delay times in seconds for retries: 10s, 20s, 30s, 1m, 2m, 5m
    protected $delay_times = [10, 20, 30, 60, 120, 300];

    // Maximum number of attempts
    public $tries = 3;

    protected $account;

    /**
     * Create a new job instance.
     *
     * @param BeiAccount $account The account for which to sync parametrics
     * @return void
     */
    public function __construct(BeiAccount $account)
    {
        $this->account = $account;
    }

    /**
     * Get the middleware for the job.
     * Prevents overlapping executions for the same account.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new WithoutOverlapping("sync-specific-parametrics-".$this->account->id))->releaseAfter(10)];
    }

    /**
     * Execute the job.
     * Synchronizes account-specific parametrics for the account.
     *
     * @param ParametricContract $parametricService
     * @return void
     */
    public function handle(ParametricContract $parametricService)
    {
        try {
            info("=====> Starting synchronization of SPECIFIC parametrics for account {$this->account->id}");

            // List of account-specific parametric types to sync
            $types = [
                ParametricType::ACTIVIDADES,
                ParametricType::PRODUCTOS_SIN,
                ParametricType::LEYENDAS
            ];

            foreach ($types as $type) {
                info("Syncing parametric: {$type->value} for account {$this->account->id}");
                $parametricService->sync(
                    $this->account->bei_host,
                    $this->account->bei_token,
                    $type->value,
                    $this->account->id
                );
                info("Parametric {$type->value} synced successfully for account {$this->account->id}");
            }

            info("=====> Finished synchronization of SPECIFIC parametrics for account {$this->account->id}");
        } catch (\Throwable $ex) {
            info("Error syncing specific parametrics for account {$this->account->id}: {$ex->getMessage()} in {$ex->getFile()}:{$ex->getLine()}");

            // Release with exponential backoff if attempts remain
            if ($this->attempts() < $this->tries) {
                $attempts_after = $this->delay_times[$this->attempts() - 1] ?? 300;
                $this->release($attempts_after);
            }
        }
    }

    /**
     * Handle job failure after all retries are exhausted.
     *
     * @param Throwable $exception
     * @return void
     */
    public function failed(Throwable $exception)
    {
        info("Job SyncSpecificParametrics failed for account {$this->account->id} after {$this->tries} attempts: {$exception->getMessage()}");
        // TODO: Send notification or alert for manual intervention
    }
}
