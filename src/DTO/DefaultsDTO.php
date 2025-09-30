<?php

namespace Emizor\SDK\DTO;

use Emizor\SDK\Exceptions\EmizorApiDefaultsValidationException;
use Emizor\SDK\Rules\CheckParametricRule;
use Emizor\SDK\Rules\RuleCheckTypeDocuments;
use Illuminate\Support\Facades\Validator;

final class DefaultsDTO
{
    public function __construct(
        private ?string $typeDocument,
        private ?string $branch,
        private ?string $pos,
        private ?string $paymentMethod,
        private ?string $reasonRevocation,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $data = [
            'type_document' => $this->typeDocument,
            'branch' => $this->branch,
            'pos' => $this->pos,
            'payment_method' => $this->paymentMethod,
            'reason_revocation' => $this->reasonRevocation,
        ];

        $rules = [
            'type_document' => ['nullable', 'string', new RuleCheckTypeDocuments()],
            'branch' => ['nullable', 'integer','min:0','max:20'],
            'pos' => ['nullable', 'integer',"min:0","max:100"],
            'payment_method' => ['nullable', 'string', new CheckParametricRule("payment_method")],
            'reason_revocation' => ['nullable', 'integer', "in:1,3"],
        ];

        $messages = [
            'type_document.string' => 'El campo tipo de documento debe ser texto válido.',
            'branch.integer' => 'La sucursal debe ser un número entero.',
            'branch.min'     => 'La sucursal debe ser como mínimo 0.',
            'branch.max'     => 'La sucursal no puede ser mayor a 20.',
            'pos.integer' => 'El punto de venta debe ser un número entero.',
            'pos.min'     => 'El punto de venta debe ser como mínimo 0.',
            'pos.max'     => 'El punto de venta no puede ser mayor a 100.',
            'payment_method.string' => 'El método de pago debe ser texto válido.',
            'reason_revocation.integer' => 'La razón de revocación debe ser un número entero.',
            'reason_revocation.in'      => 'La razón de revocación solo puede ser 1 (error de facturación) o 3 (anulación por otro motivo).',
        ];
        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' | ', $errors);
            throw new EmizorApiDefaultsValidationException("Errores de validación en los defaults: " . $errorMessage);
        }
    }

    public function toArray(): array
    {
        return [
            'type_document' => $this->typeDocument ?? null,
            'branch' => $this->branch ?? null,
            'pos' => $this->pos ?? null,
            'payment_method' => $this->paymentMethod ?? null,
            'reason_revocation' => $this->reasonRevocation ?? null,
        ];
    }
}
