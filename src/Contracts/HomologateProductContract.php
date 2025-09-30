<?php

namespace Emizor\SDK\Contracts;

use Emizor\SDK\DTO\HomologateProductDTO;

interface HomologateProductContract
{
    /**
     * Homologate a code product with product sin an activity code from fiscal entity
     * @return void
     */
    public function homologate(array $products, string $accountId):void;

    /**
     * List homologate products
     * @var string $accountId
     */
    public function listHomologate(string $accountId):array;
}
