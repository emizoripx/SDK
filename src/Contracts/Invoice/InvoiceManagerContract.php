<?php

namespace Emizor\SDK\Contracts\Invoice;

use Closure;
use Emizor\SDK\Entities\BeiInvoiceEntity;
use Emizor\SDK\Models\BeiAccount;

interface InvoiceManagerContract
{
    /**
     * @param Closure $callback
     * @param string $ticket
     * @param BeiAccount $beiAccount
     * @return void
     */
    public function createAndEmitInvoice(Closure $callback, string $ticket, BeiAccount $beiAccount): void;


    /**
     * @param string $ticket
     * @param int $revocationReasonCode
     * @return void
     */
    public function revokeInvoice(string $ticket, int $revocationReasonCode):void;


    /**
     * @param string $ticket
     * @return BeiInvoiceEntity
     */
    public function getInvoice(string $ticket):BeiInvoiceEntity;

}
