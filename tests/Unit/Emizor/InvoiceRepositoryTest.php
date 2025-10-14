<?php

use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Repositories\InvoiceRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('returns null when ticket not found', function () {
    $repository = new InvoiceRepository();

    $result = $repository->findByTicket('non-existent-ticket');

    expect($result)->toBeNull();
});

it('returns invoice when ticket exists', function () {
    $invoice = BeiInvoice::factory()->create(['bei_ticket' => 'test-ticket']);

    $repository = new InvoiceRepository();

    $result = $repository->findByTicket('test-ticket');

    expect($result)->toBeInstanceOf(BeiInvoice::class);
    expect($result->bei_ticket)->toBe('test-ticket');
});