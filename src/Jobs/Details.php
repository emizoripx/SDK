<?php

namespace Emizor\SDK\Jobs;

use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Services\Invoice\InvoiceDetailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;

class Details implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 4;
    public $backoff = [0.1,0.1,0.1,0.1,0.1,0.1,0.5,0.5,1,1,1,5,10,10,10,10,10,30];

    protected $ticket;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
    }


    public function middleware()
    {
        return [(new WithoutOverlapping($this->ticket))->releaseAfter(10)];
    }
    public function retryUntil()
    {
        return now()->addHours(72);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InvoiceDetailService $invoiceDetailService)
    {
        try {

            $invoiceDetailService->getDetails($this->ticket);

        } catch (Throwable $ex) {
            info("Error  TICKET: " . $this->ticket." Message: ". $ex->getMessage() . " File : " . $ex->getFile() . " Line: " .$ex->getLine());
//            $attempts_after = $this->delay_times[$this->atgit tempts() - 1];

            $this->releaseBackoff();
        }
    }
    public function releaseBackoff()
    {
        if ($this->attempts() > sizeof($this->backoff)) {
            $retry_time= $this->backoff[sizeof($this->backoff)-1] * 60 ;
        }else {
            $retry_time= $this->backoff[$this->attempts()-1] * 60 ;
        }

        info("CHECK-TRANSACTION  #" . $this->transaction_id . " >>> " . " RELEASE FOR " . $retry_time . " seconds");
        $this->release($retry_time);
    }


    public function failed(Throwable $exception)
    {
        info(' ocurrio un error en realizar la petición de Estado Exception: ' . $exception->getMessage());
    }
}
