<?php

namespace Emizor\SDK\Contracts\Invoice;

interface InvoiceEmissionContract
{

    /**
     * @param string $ticket
     * @return void
     */
    public function emit(string $ticket): void;
}
