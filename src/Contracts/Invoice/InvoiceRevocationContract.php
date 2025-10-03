<?php

namespace Emizor\SDK\Contracts\Invoice;

interface InvoiceRevocationContract
{

    /**
     * @param string $ticket
     * @param int $revocationReasonCode
     * @return void
     */
    public function revocate(string $ticket, int $revocationReasonCode): void;
}
