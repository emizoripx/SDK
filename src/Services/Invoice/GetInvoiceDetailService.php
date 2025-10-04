<?php

namespace Emizor\SDK\Services\Invoice;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\GetInvoiceDetailContract;

class GetInvoiceDetailService implements GetInvoiceDetailContract
{

    protected EmizorApiHttpContract $emizorApiHttpService;

    public function __construct(EmizorApiHttpContract $emizorApiHttpService)
    {
        $this->emizorApiHttpService = $emizorApiHttpService;
    }

    public function getDetail(string $host, string $token, string $ticket): array
    {
        return $this->emizorApiHttpService->getDetailInvoice($host, $token, $ticket);

    }
}
