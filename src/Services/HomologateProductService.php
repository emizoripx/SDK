<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\HomologateProductContract;
use Emizor\SDK\Repositories\HomologateProductRepository;
use Emizor\SDK\Validators\HomologateProductsValidator;

class HomologateProductService implements HomologateProductContract
{

    protected HomologateProductRepository $repository;

    protected HomologateProductsValidator $validator;

    public function __construct(HomologateProductRepository $repository, HomologateProductsValidator $validator)
    {
        $this->repository = $repository;

        $this->validator = $validator;
    }

    /**
     * array ProductDTO
     */
    public function homologate(array $products, string $accountId): void
    {
        $this->validator->validate($products);
        $this->repository->store($products, $accountId);

    }

    public function listHomologate($accountId): array
    {
        return $this->repository->list($accountId);
    }
}
