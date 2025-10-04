<?php

namespace Emizor\SDK\Jobs;

use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Models\BeiAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

/**
 * Job to synchronize a specific parametric type for a specific account.
 *
 * This job fetches and stores a single parametric type for the account.
 * It uses exponential backoff for retries and prevents overlapping executions.
 */
class SyncParametric implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Delay times in seconds for retries: 10s, 20s, 30s, 1m, 2m, 5m
    protected $delay_times = [10, 20, 30, 60, 120, 300];

    // Maximum number of attempts
    public $tries = 3;

    protected $account;
    protected $parametricType;

    /**
     * Create a new job instance.
     *
     * @param BeiAccount $account The account for which to sync the parametric
     * @param string $parametricType The type of parametric to sync
     * @return void
     */
    public function __construct(BeiAccount $account, string $parametricType)
    {
        $this->account = $account;
        $this->parametricType = $parametricType;
    }

    /**
     * Get the middleware for the job.
     * Prevents overlapping executions for the same account and type.
     *
     * @return array
     */
    public function middleware()
    {
        return [(new WithoutOverlapping("sync-parametric-{$this->account->id}-{$this->parametricType}"))->releaseAfter(10)];
    }

    /**
     * Execute the job.
     * Synchronizes the specified parametric type for the account.
     *
     * @param ParametricContract $parametricService
     * @return void
     */
    public function handle(ParametricContract $parametricService)
    {
        try {
            info("=====> Starting synchronization of parametric '{$this->parametricType}' for account {$this->account->id}");

            $parametricService->sync(
                $this->account->bei_host,
                $this->account->bei_token,
                $this->parametricType,
                $this->account->id
            );

            info("=====> Parametric '{$this->parametricType}' synced successfully for account {$this->account->id}");
        } catch (\Throwable $ex) {
            info("Error syncing parametric '{$this->parametricType}' for account {$this->account->id}: {$ex->getMessage()} in {$ex->getFile()}:{$ex->getLine()}");

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
        info("Job SyncParametric failed for parametric '{$this->parametricType}' and account {$this->account->id} after {$this->tries} attempts: {$exception->getMessage()}");
        // TODO: Send notification or alert for manual intervention
    }
}