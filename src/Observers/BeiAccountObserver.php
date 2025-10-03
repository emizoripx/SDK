<?php

namespace Emizor\SDK\Observers;

use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Services\TokenManager;

class BeiAccountObserver
{
    protected TokenManager $tokenManager;

    public function __construct(TokenManager $tokenManager)
    {
        $this->tokenManager = $tokenManager;
    }
    /**
     * Handle the BeiAccount "created" event.
     */
    public function created(BeiAccount $account): void
    {
        $this->tokenManager->generateAndSaveToken($account);
    }
}
