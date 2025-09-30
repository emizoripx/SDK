<?php

namespace Emizor\SDK\Repositories;

use Emizor\SDK\DTO\HomologateProductDTO;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiGlobalParametric;
use Emizor\SDK\Models\BeiProduct;

class HomologateProductRepository
{
    public function store(array $products, string $accountId)
    {
        foreach ($products as $item) {
            BeiProduct::updateOrCreate(
                [
                    "bei_product_code" => (string)$item["bei_product_code"],
                    "bei_account_id" => $accountId,
                ],
                [
                    "bei_product_code" => (string)$item["bei_product_code"],
                    "bei_sin_product_code" => (string)$item["bei_sin_product_code"],
                    "bei_activity_code" => (string)$item["bei_activity_code"],
                    "bei_unit_code" => (string) $item["bei_unit_code"],
                    "bei_unit_name" => (string) BeiGlobalParametric::where("bei_type", ParametricType::UNIDADES->value)->where("bei_code", $item["bei_unit_code"])->first()->bei_description,
                    "bei_account_id" => $accountId,
                ]
            );
        }
    }

    public function list($accountId)
    {
        return BeiProduct::where("bei_account_id", $accountId)->get()?->map(fn ($m) => HomologateProductDTO::from($m)->toArray())->toArray();
    }
}
