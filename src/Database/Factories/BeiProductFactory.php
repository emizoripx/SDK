<?php

namespace Emizor\SDK\Database\Factories;


use Emizor\SDK\Models\BeiProduct;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BeiProductFactory extends Factory
{
    protected $model = BeiProduct::class;

    public function definition()
    {
        return [
            'id' => (string) Str::uuid(),
            'bei_account_id' => (string) Str::uuid(),
            'bei_product_code' => $this->faker->unique()->lexify('PRODUCT_CODE_????'),
            'bei_sin_product_code' => $this->faker->unique()->lexify('PRODUCT_SIN_????'),
            'bei_activity_code' => $this->faker->unique()->lexify('ACTIVITY_????'),
            'bei_unit_code' => $this->faker->unique()->lexify('UNIT_????'),
            'bei_unit_name' => $this->faker->unique()->lexify('UNIT_NAME_????'),
        ];
    }
}
