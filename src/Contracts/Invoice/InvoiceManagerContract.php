<?php

namespace Emizor\SDK\Contracts\Invoice;

use Closure;

interface InvoiceManagerContract
{
    /**
     * @param Closure $callback
     * @param string $ticket
     * @param string $accountId
     * @return void
     */
    public function createAndEmitInvoice(Closure $callback, string $ticket, string $accountId): void;


    /**
     * @param string $ticket
     * @param int $revocationReasonCode
     * @return void
     */
    public function revocateInvoice(string $ticket, int $revocationReasonCode):void;

}
