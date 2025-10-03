<?php

namespace Emizor\SDK\Contracts;

use Emizor\SDK\Models\BeiInvoice;

interface GetInvoiceDetailContract
{

    /**
     * Get detail of an invoice
     *
     * @param string $host
     * @param string $token
     * @param string $ticket
     * @return array
     */
    public function getDetail(string $host, string $token, string $ticket): array;
}
