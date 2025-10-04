<?php

namespace Emizor\SDK\Jobs;

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
 * Job to ensure a valid token exists for a specific account.
 *
 * This job generates and saves a new access token for the account if needed.
 * It uses exponential backoff for retries and prevents overlapping executions.
 */
class EnsureToken implements ShouldQueue
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
     * @param BeiAccount $account The account for which to ensure token
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
        return [(new WithoutOverlapping("ensure-token-".$this->account->id))->releaseAfter(10)];
    }

    /**
     * Execute the job.
     * Generates and saves a new token for the account.
     *
     * @param TokenManager $tokenManager
     * @return void
     */
    public function handle(TokenManager $tokenManager)
    {
        try {
            info("=====> Starting token generation for account {$this->account->id}");
            $tokenManager->generateAndSaveToken($this->account);
            info("=====> Token generated and saved successfully for account {$this->account->id}");
        } catch (\Throwable $ex) {
            info("Error generating token for account {$this->account->id}: {$ex->getMessage()} in {$ex->getFile()}:{$ex->getLine()}");

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
        info("Job EnsureToken failed for account {$this->account->id} after {$this->tries} attempts: {$exception->getMessage()}");
        // TODO: Send notification or alert for manual intervention
    }
}
