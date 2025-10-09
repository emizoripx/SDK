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

    public function register(Closure $callback): string
    {
        $builder = new RegisterBuilder();
        $callback($builder);
        $dto = $builder->build();

        $account = $this->repository->updateOrCreate(
            [
                'owner_type' => $dto->ownerType,
                'owner_id' => $dto->ownerId,
            ],
            [
                'bei_client_id' => $dto->clientId,
                'bei_client_secret' => $dto->clientSecret,
                'bei_host' => $dto->host,
            ]
        );

        return $account->id;
    }
}