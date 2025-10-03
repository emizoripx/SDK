<?php

namespace Emizor\SDK\DTO;

use Illuminate\Support\Facades\Validator;
use Emizor\SDK\Exceptions\EmizorApiDefaultsValidationException;

final class InvoiceDetailDTO
{
    public function __construct(
        private string $product_code,
        private string $description,
        private float $quantity,
        private float $unit_price,
        private ?string $unit_code = null
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $data = [
            'product_code' => $this->product_code,
            'description'  => $this->description,
            'quantity'     => $this->quantity,
            'unit_price'   => $this->unit_price,
            'unit_code'    => $this->unit_code,
        ];

        $rules = [
            'product_code' => ['required', 'string'],
            'description'  => ['required', 'string'],
            'quantity'     => ['required', 'numeric', 'min:0.01'],
            'unit_price'   => ['required', 'numeric', 'min:0'],
            'unit_code'    => ['nullable', 'string'],
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
            'product_code' => $this->product_code,
            'description'  => $this->description,
            'quantity'     => $this->quantity,
            'unit_price'   => $this->unit_price,
            'unit_code'    => $this->unit_code,
        ];
    }
}
