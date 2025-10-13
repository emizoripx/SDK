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

        $validator = Validator::make($data, $rules);

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
