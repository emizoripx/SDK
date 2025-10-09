<?php

namespace Emizor\SDK\Database\Factories;


use Emizor\SDK\Models\BeiAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BeiAccountFactory extends Factory
{
    protected $model = BeiAccount::class;

    public function definition()
    {
        return [
            'id' => (string) Str::uuid(),
            'bei_enable' => true,
            'bei_verified_setup' => true,
            'bei_client_id' => $this->faker->unique()->lexify('CLIENT_????'),
            'bei_client_secret' => $this->faker->password(),
            'bei_host' => $this->faker->randomElement(['PILOTO', 'PRODUCTION']),
            'bei_demo' => true,
            'owner_type' => null,
            'owner_id' => null,
        ];
    }
}
