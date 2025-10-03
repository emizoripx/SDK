<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Models\BeiAccount;

class TokenManager
{
    protected AccountRepository $repository;
    private TokenContract $tokenService;

    public function __construct(TokenContract $tokenService, AccountRepository $repository)
    {
        $this->tokenService = $tokenService;
        $this->repository = $repository;
    }

    public function generateAndSaveToken(BeiAccount $account): void
    {
        $response = $this->tokenService->generate($account->bei_host,$account->bei_client_id, $account->bei_client_secret);

        $this->repository->saveToken($account->id, $response["token"],$response["expires_at"]);
    }
}
