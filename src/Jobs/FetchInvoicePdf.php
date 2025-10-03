<?php

namespace Emizor\SDK\Jobs;

use Emizor\SDK\Contracts\GetInvoiceDetailContract;
use Emizor\SDK\Events\InvoicePdfRetrieved;
use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Services\Invoice\InvoiceDetailService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class FetchInvoicePdf implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public BeiInvoice $invoice;

    public function __construct(BeiInvoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function handle(GetInvoiceDetailContract $getInvoiceDetailService)
    {

        $details = $getInvoiceDetailService->getDetail($this->invoice->bei_account->bei_host,$this->invoice->bei_account->bei_token, $this->invoice->bei_ticket);

        info("check details =============>>>>>>>" );

        if (isset($details['status']) && $details["status"] == "success") {
            $additional = $this->invoice->getAdditional() ?? [];
            $additional['pdf_url'] = $details["data"]['pdf_url'];
            info("additionals ", [$additional]);
            $this->invoice->bei_additional = $additional;
            $this->invoice->saveQuietly();

            event(new InvoicePdfRetrieved($this->invoice));
        }
    }
}
