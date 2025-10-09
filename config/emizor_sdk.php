<?php

return [
    /**
     * Allowed owner types for polymorphic relations
     * Add your models here, e.g., 'company' => App\Models\Company::class
     */
    'owners' => [
        // 'company' => App\Models\Company::class,
        // 'user' => App\Models\User::class,
    ],

    /**
     * Register your listeners to event services
     *
     */
    "listeners" => [
        /**
         * Data receive in listeners :
         * string $ticket
         * string $status
         * array $meta , reason revocation, reason of rejection
         */
        \Emizor\SDK\Events\InvoiceRejected::class => [
            // Example listener \Emizor\SDK\Listeners\SendInvoiceNotification::class,
        ],
        \Emizor\SDK\Events\InvoiceInProcess::class => [],
        \Emizor\SDK\Events\InvoiceAccepted::class => [],
        \Emizor\SDK\Events\InvoiceRevocated::class => [],
        \Emizor\SDK\Events\InvoiceReverted::class => [],
    ]

];
