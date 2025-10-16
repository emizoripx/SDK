<?php

namespace Emizor\SDK\DTO;

use Illuminate\Support\Facades\Validator;
use Emizor\SDK\Exceptions\EmizorApiDefaultsValidationException;

final class InvoiceDetailDTO
{
    public function __construct(
        private string $productCode,
        private string $description,
        private float $quantity,
        private float $unitPrice,
        private ?string $unitCode = null,
        private ?string $sinProductCode = null,
        private ?string $activityCode = null
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $data = [
            'product_code' => $this->productCode,
            'description'  => $this->description,
            'quantity'     => $this->quantity,
            'unit_price'   => $this->unitPrice,
            'unit_code'    => $this->unitCode,
            'sin_product_code' => $this->sinProductCode,
            'activity_code' => $this->activityCode,
        ];

        $rules = [
            'product_code' => ['required', 'string'],
            'description'  => ['required', 'string'],
            'quantity'     => ['required', 'numeric', 'min:0.01'],
            'unit_price'   => ['required', 'numeric', 'min:0'],
            'unit_code'    => ['nullable', 'string'],
            'sin_product_code'    => ['required', 'string'],
            'activity_code'    => ['required', 'string'],
        ];

        $messages = [
            'product_code.required'     => 'El código del producto es obligatorio.',
            'product_code.string'       => 'El código del producto debe ser un texto.',

            'description.required'      => 'La descripción es obligatoria.',
            'description.string'        => 'La descripción debe ser un texto.',

            'quantity.required'         => 'La cantidad es obligatoria.',
            'quantity.numeric'          => 'La cantidad debe ser un número.',
            'quantity.min'              => 'La cantidad debe ser mayor a cero.',

            'unit_price.required'       => 'El precio unitario es obligatorio.',
            'unit_price.numeric'        => 'El precio unitario debe ser un número.',
            'unit_price.min'            => 'El precio unitario no puede ser negativo.',

            'unit_code.string'          => 'El código de unidad debe ser un texto.',

            'sin_product_code.required' => 'El código del producto no esta homologado, necesita CODIGO PRODUCTO SIN',
            'sin_product_code.string'   => 'El código SIN del producto debe ser un texto.',

            'activity_code.required'    => 'El código del producto no esta homologado, necesita CÓDIGO ACTIVIDAD',
            'activity_code.string'      => 'El código de actividad debe ser un texto.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new EmizorApiDefaultsValidationException(
                "Errores en detalle: " . implode(' | ', $validator->errors()->all())
            );
        }
    }

    public function toArray(): array
    {
        return [
            'product_code' => $this->productCode,
            'description'  => $this->description,
            'quantity'     => $this->quantity,
            'unit_price'   => $this->unitPrice,
            'unit_code'    => $this->unitCode,
            'sin_product_code' => $this->sinProductCode,
            'activity_code' => $this->activityCode
        ];
    }
}
