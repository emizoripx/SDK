<?php

namespace Emizor\SDK\Events;

use Emizor\SDK\Models\BeiInvoice;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoicePdfRetrieved
{
    use Dispatchable, SerializesModels;

    public BeiInvoice $invoice;

    public function __construct(BeiInvoice $invoice)
    {
        $this->invoice = $invoice;
    }
}