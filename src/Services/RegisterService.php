<?php

namespace Emizor\SDK\Services;

use Closure;
use Emizor\SDK\Builders\RegisterBuilder;
use Emizor\SDK\Contracts\RegisterContract;
use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\AccountRepository;

class RegisterService implements RegisterContract
{
    private AccountRepository $repository;

    public function __construct(AccountRepository $repository)
    {
        $this->repository = $repository;
    }

    public function register(Closure $callback): BeiAccount
    {
        $builder = new RegisterBuilder();
        $callback($builder);
        $dto = $builder->build();

        return $this->repository->create([
            'bei_client_id' => $dto->clientId,
            'bei_client_secret' => $dto->clientSecret,
            'bei_host' => $dto->host,
        ]);
    }
}