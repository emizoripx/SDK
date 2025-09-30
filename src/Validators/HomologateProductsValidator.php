<?php

namespace Emizor\SDK\Validators;

use Emizor\SDK\DTO\HomologateProductDTO;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Exceptions\ParametricSyncValidationException;


class HomologateProductsValidator
{
    public function validate(array $products): void
    {

        foreach($products as $product) {
            new HomologateProductDTO(
    $product["bei_product_code"]??"",
        $product["bei_sin_product_code"]??"",
    $product["bei_activity_code"]??"",
    $product["bei_unit_code"]??"",
            );
        }



    }
}
