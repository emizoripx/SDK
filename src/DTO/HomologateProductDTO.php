<?php

namespace Emizor\SDK\DTO;

use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Exceptions\EmizorApiDefaultsValidationException;
use Emizor\SDK\Models\BeiProduct;
use Emizor\SDK\Rules\CheckParametricRule;
use Illuminate\Support\Facades\Validator;

final class HomologateProductDTO
{
    public function __construct(
        private string $bei_product_code,
        private string $bei_sin_product_code,
        private string $bei_activity_code,
        private string $bei_unit_code,
        private string $bei_unit_name,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $data = [
            'bei_product_code'    => $this->bei_product_code,
            'bei_sin_product_code'=> $this->bei_sin_product_code,
            'bei_activity_code'   => $this->bei_activity_code,
            'bei_unit_code'       => $this->bei_unit_code,
        ];

        $rules = [
            'bei_product_code'    => ["required", "string"],
            'bei_activity_code'   => ["required", "string", new CheckParametricRule(ParametricType::ACTIVIDADES, false)],
            'bei_sin_product_code'=> ["required", "string", new CheckParametricRule(ParametricType::PRODUCTOS_SIN, false)],
            'bei_unit_code'       => ["required", "string", new CheckParametricRule(ParametricType::UNIDADES)],
        ];

        $messages = [
            'bei_product_code.required' => 'El "bei_product_code" es obligatorio.',
            'bei_activity_code.required' => 'El "bei_activity_code" es obligatorio.',
            'bei_sin_product_code.required' => 'El "bei_sin_product_code" SIN es obligatorio.',
            'bei_unit_code.required' => 'La "bei_unit_code" es obligatorio.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' | ', $errors);
            throw new EmizorApiDefaultsValidationException(
                "Errores de validación en la homologación de productos: " . $errorMessage
            );
        }
    }
    public static function from(BeiProduct $model): self
    {
        return new self(
            bei_product_code: $model->bei_product_code,
            bei_sin_product_code: $model->bei_sin_product_code,
            bei_activity_code: $model->bei_activity_code,
            bei_unit_code: $model->bei_unit_code,
            bei_unit_name: $model->bei_unit_name,
        );
    }
    public function toArray(): array
    {
        return [
            'bei_product_code'     => $this->bei_product_code,
            'bei_sin_product_code' => $this->bei_sin_product_code,
            'bei_activity_code'    => $this->bei_activity_code,
            'bei_unit_code'        => $this->bei_unit_code,
            'bei_unit_name'        => $this->bei_unit_name,
        ];
    }

}
