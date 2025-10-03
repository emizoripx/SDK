<?php

namespace Emizor\SDK\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;

class TrackOffline implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1;

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
    public function handle()
    {
        info("TRACK-OFFLINE with TICKET: " . $this->ticket);
        try {
            Details::dispatch($this->ticket);

        } catch (\Throwable $ex) {
            info("TRACK-OFFLINE Error : ". $this->ticket." Message: " . $ex->getMessage() . " File : " . $ex->getFile() . " Line: " . $ex->getLine());
        }

        return 0;
    }

    public function failed(Throwable $exception)
    {
        info(' ocurrio un error en realizar la petición de Estado Exception: ' . $exception->getMessage());
    }
}
