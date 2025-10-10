<?php

namespace Emizor\SDK\Contracts\Invoice;

use Closure;
use Emizor\SDK\Entities\BeiInvoiceEntity;

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


    /**
     * @param string $ticket
     * @return BeiInvoiceEntity
     */
    public function getInvoice(string $ticket):BeiInvoiceEntity;

}
