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

    protected $ticket;

    protected $invoice;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($ticket)
    {
        $this->ticket = $ticket;
        $this->invoice = BeiInvoice::findByTicket($this->ticket);
    }

    public function backoff()
    {
        return [2, 5, 10, 30];
    }


    public function middleware()
    {
        return [(new WithoutOverlapping($this->ticket))->releaseAfter(10)];
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
            $attempts_after = $this->delay_times[$this->attempts() - 1];

            $this->release($attempts_after);
        }
    }


    public function failed(Throwable $exception)
    {
        info(' ocurrio un error en realizar la petición de Estado Exception: ' . $exception->getMessage());
    }
}
