<?php

namespace Emizor\SDK\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class EmizorApiEventServiceProvider extends EventServiceProvider
{
    protected $listen = [
        \Emizor\SDK\Events\InvoiceIssued::class => [
            \Emizor\SDK\Listeners\SendInvoiceNotification::class,
        ],
    ];
}
