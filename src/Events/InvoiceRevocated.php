<?php

namespace Emizor\SDK\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvoiceRevocated
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly string $ticket,
        public readonly string $status,
        public readonly array $meta = []
    ) {}
}
