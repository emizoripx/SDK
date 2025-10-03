<?php

namespace Emizor\SDK\Database\Factories;


use Emizor\SDK\Models\BeiInvoice;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BeiInvoiceFactory extends Factory
{
    protected $model = BeiInvoice::class;

    public function definition()
    {
        return [
            'bei_account_id' => (string) Str::uuid(),
            'bei_step_emission' => 'none',
            'bei_step_revocation' => 'none',
            'bei_amount_total' => $this->faker->randomFloat(2, 2, 4000),
            'bei_sector_document_id' => 1,
            'bei_pos_code' => 0,
            'bei_branch_code' => 0,
            'bei_payment_method' => 1,
            'bei_client' => [
                'bei_client_code'                 => (string) Str::uuid(),
                'bei_client_document_number'      => $this->faker->numberBetween(77777777, 99999999),
                'bei_client_business_name'        => $this->faker->name(),
                'bei_client_complement'           => null,
                'bei_client_document_number_type' => 1,
            ],
            'bei_details' => [],
            'bei_additional' => [],
            'bei_emission_date' => null,
            'bei_cuf' => null,
            'bei_online' => 1,
            'bei_pdf_url' => null,
            'bei_giftcard_amount' => 0,
            'bei_exception_code' => 0
        ];
    }
}
