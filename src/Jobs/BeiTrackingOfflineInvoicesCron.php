<?php

namespace Emizor\SDK\Jobs;

use Carbon\Carbon;
use Emizor\SDK\Models\BeiOfflineInvoiceTracking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BeiTrackingOfflineInvoicesCron implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /* Get all invoices where the send date is less than NOW + 30 minutes() */
        $start = Carbon::now()->format('Y-m-d h:i:s');
        info('tracking  offline invoices '. $start);
        $offline_invoices_tracking = BeiOfflineInvoiceTracking::where('next_try', '<=', Carbon::now()->timezone('America/La_Paz')->toDateTimeString())->cursor();

        $offline_invoices_tracking->each(function ($offline_invoice, $key) {

            if ($offline_invoice->reachMaxTries()) {
                // notify email error in max tries of offline invoice
                info("NOTIFICANDO EL TICKET  ERROR EN INTENTOS >>>>>>>>>>>>" . $offline_invoice->ticket);
            } else {

                info("Tracking " . $offline_invoice->ticket);

                $offline_invoice->setupNextTry();

                TrackOffline::dispatch($offline_invoice->ticket);
            }

        });
        info("Tracking offline invoices duration " . $start . " - " . Carbon::now()->format('Y-m-d h:i:s'));
    }
}
