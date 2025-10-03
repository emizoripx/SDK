<?php

namespace Emizor\SDK\Jobs;

use Emizor\SDK\Contracts\Invoice\InvoiceRevocationContract;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;

class Revocate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 6;

    protected $ticket;

    protected  $revocationReasonCode;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(string $ticket, int $revocationReasonCode)
    {
        $this->ticket = $ticket;
        $this->revocationReasonCode = $revocationReasonCode;
    }


    public function middleware()
    {
        return [(new WithoutOverlapping($this->ticket))->releaseAfter(10)];
    }

    public function backoff()
    {
        return [10, 20, 30, 60, 120, 300];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InvoiceRevocationContract $invoiceRevocationService)
    {
        try {
            $invoiceRevocationService->revocate($this->ticket, $this->revocationReasonCode);
        } catch (\Throwable $ex) {
            info("Error  " . $ex->getMessage() . " File : " . $ex->getFile() . " Line: " . $ex->getLine());
            $this->release($this->backoff()[$this->attempts() - 1]);
        }
    }



    public function failed(Throwable $exception)
    {
        info(' ocurrio un error en realizar la petición de Estado Exception: ' . $exception->getMessage());
    }
}
