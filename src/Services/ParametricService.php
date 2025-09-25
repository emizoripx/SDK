<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Repositories\ParametricRepository;

class ParametricService implements ParametricContract
{
    protected HttpClientInterface $http;
    protected ParametricRepository $repository;

    public function __construct(HttpClientInterface $http, ParametricRepository $repository)
    {
        $this->http = $http;
        $this->repository = $repository;
    }

    public function sync( $type, string $accountId): void
    {
        $endpoint = "/api/v1/parametricas/" . $type;
        $response = $this->http->get($endpoint);

        $this->repository->store($type, $response['data'], $accountId);
    }

    public function get( $type, string $accountId): array
    {
        return $this->repository->list($type, $accountId);
    }
}
