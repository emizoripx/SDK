<?php

namespace Emizor\SDK\Jobs;

use Emizor\SDK\Services\Invoice\InvoiceNitValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Throwable;


class ValidateNit implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $delay_times = [10, 20, 30, 60, 120, 300];

    public $tries = 1;

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

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(InvoiceNitValidationService $invoiceNitValidationService)
    {
        try {
            info("inicia validarndo nit");
            $invoiceNitValidationService->validaNit($this->ticket);
            info("termina validarndo nit");
        } catch (\Throwable $ex) {
            info("Error  " . $ex->getMessage() . " File : " . $ex->getFile() . " Line: " . $ex->getLine());
            $attempts_after = $this->delay_times[$this->attempts() - 1];

            $this->release($attempts_after);
        }
    }

    public function failed(Throwable $exception)
    {
        info(' ocurrio un error en realizar la petición de Estado Exception: ' . $exception->getMessage());
    }
}
