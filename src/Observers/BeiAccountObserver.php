<?php

namespace Emizor\SDK\Observers;

use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\AccountRepository;

class BeiAccountObserver
{

    protected AccountRepository $repository;
    protected HttpClientInterface $http;
    protected TokenContract $tokenService;

    public function __construct( AccountRepository $repository = null, TokenContract $tokenService)
    {
        $this->repository = $repository ?? new AccountRepository();
        $this->tokenService = $tokenService;
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
         $tokenData = $this->tokenService
            ->setHost($account->bei_host)
            ->generate(
                $account->bei_client_id,
                $account->bei_client_secret
            );

        $this->repository->saveToken($account->id, $tokenData['token'], $tokenData['expires_at']);
    }
}
