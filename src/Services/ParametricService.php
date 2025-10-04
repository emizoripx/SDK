<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Enums\ParametricType;
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

    public function sync(string $host, string $token, $type, string $accountId): void
    {
        $response = $this->emizorApiHttp->getParametrics($host, $token, $type);

        $this->repository->store($type, $response['data'], $accountId);
    }

    public function get( $type, string $accountId): array
    {
        return $this->repository->list($type, $accountId);
    }

    public function listParametricTypes()
    {
        return ParametricType::cases();
    }
}
