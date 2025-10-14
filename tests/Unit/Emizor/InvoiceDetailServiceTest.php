<?php

use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Services\Invoice\InvoiceDetailService;
use Emizor\SDK\Models\BeiOfflineInvoiceTracking;
use Emizor\SDK\Contracts\EmizorApiHttpContract;

it('calls updateBEIfields after markCompleteEmission when invoice is in progress', function () {
    $http = Mockery::mock(EmizorApiHttpContract::class);
    $invoice = Mockery::mock(BeiInvoice::class)->makePartial();
    $invoice->bei_ticket = 'ticket123';
    $invoice->shouldReceive('isInProgress')->andReturn(true);
    $invoice->shouldReceive('markCompleteEmission')->once()->ordered();
    $invoice->shouldReceive('updateBEIfields')->once()->ordered()->with(['estado' => 'INVOICE_STATE_COMPLETE']);

    $service = new InvoiceDetailService($http);

    $response = ['estado' => 'INVOICE_STATE_COMPLETE'];

    $service->processResponse($invoice, $response);
});

it('calls updateBEIfields after markRejected when invoice is invalid', function () {
    $http = Mockery::mock(EmizorApiHttpContract::class);
    $invoice = Mockery::mock(BeiInvoice::class)->makePartial();
    $invoice->bei_ticket = 'ticket123';
    $invoice->shouldReceive('isInProgress')->andReturn(false);
    $invoice->shouldReceive('updateBEIfields')->once()->with(['estado' => 'INVOICE_STATE_SENT_TO_SIN_INVALID']);

    // Mock static method
    Mockery::mock('alias:Emizor\SDK\Models\BeiOfflineInvoiceTracking')
        ->shouldReceive('remove')
        ->once()
        ->with('ticket123');

    $service = new InvoiceDetailService($http);

    $response = ['estado' => 'INVOICE_STATE_SENT_TO_SIN_INVALID'];

    $service->processResponse($invoice, $response);
});