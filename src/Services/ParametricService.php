<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\ParametricRepository;

class ParametricService implements ParametricContract
{
    protected EmizorApiHttpContract $emizorApiHttp;
    protected ParametricRepository $repository;

    public function __construct(EmizorApiHttpContract $emizorApiHttp, ParametricRepository $repository)
    {
        $this->emizorApiHttp = $emizorApiHttp;
        $this->repository = $repository;
    }

    public function syncAndSaveParametric(BeiAccount $beiAccount, string $type): void
    {
        $response = $this->emizorApiHttp->getParametrics($beiAccount->bei_host, $beiAccount->bei_token, $type);

        $this->repository->store($type, $response['data'], $beiAccount->id);
    }

    public function getParametric(BeiAccount $beiAccount, string $type): array
    {
        return $this->repository->list($type, $beiAccount->id);
    }

    public function listParametricTypes(): array
    {
        return ParametricType::cases();
    }
}
