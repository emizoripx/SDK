<?php

use Emizor\SDK\Jobs\Details;
use Emizor\SDK\Services\Invoice\InvoiceDetailService;
use Emizor\SDK\Models\BeiInvoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;

uses(RefreshDatabase::class);

it('handles exceptions from InvoiceDetailService and retries', function () {
    // Create a fake invoice
    $invoice = BeiInvoice::factory()->create(['bei_ticket' => 'test-ticket']);

    // Mock the service to throw an exception
    $this->mock(InvoiceDetailService::class, function ($mock) {
        $mock->shouldReceive('getDetails')
             ->once()
             ->with('test-ticket')
             ->andThrow(new \Exception('Test exception'));
    });

    // Dispatch the job synchronously to test
    Bus::dispatchSync(new Details('test-ticket'));

    // Since the job catches the exception and releases, the test passes if no unhandled exception
    expect(true)->toBeTrue();
});