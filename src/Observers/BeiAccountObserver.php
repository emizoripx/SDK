<?php

namespace Emizor\SDK\Observers;

use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Repositories\AccountRepository;

class BeiAccountObserver
{

    protected TokenContract $tokenService;
    protected AccountRepository $repository;

    public function __construct(TokenContract $tokenService, AccountRepository $repository = null)
    {
        $this->tokenService = $tokenService ;
        $this->repository = $repository ?? new AccountRepository();
    }

    /**
     * Handle the BeiAccount "created" event.
     */
    public function created(BeiAccount $account): void
    {
        $this->generateToken($account);
    }

    protected function generateToken(BeiAccount $account): void
    {
        $tokenData = $this->tokenService->generate(
            $account->bei_host,
            $account->bei_client_id,
            $account->bei_client_secret
        );

        $this->repository->saveToken($account->bei_client_id, $tokenData['token'], $tokenData['expires_at']);
    }
}
