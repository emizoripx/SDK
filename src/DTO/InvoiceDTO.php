<?php

namespace Emizor\SDK\DTO;

use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Exceptions\EmizorApiDefaultsValidationException;
use Emizor\SDK\Rules\CheckParametricRule;
use Emizor\SDK\Rules\RuleCheckTypeDocuments;
use Illuminate\Support\Facades\Validator;

final class InvoiceDTO
{
    public function __construct(
        private ?string $ticket,
        private ?array $client,
        private ?array $details,
        private ?string $typeDocument,
        private ?string $branch,
        private ?string $pos,
        private ?string $paymentMethod,
        private ?float $discount,
        private ?float $amount,

    ) {
        $this->validate();
    }

    private function validate(): void
    {
        $data = [
            'ticket' => $this->ticket,
            'client' => $this->client,
            'details' => $this->details,
            'type_document' => $this->typeDocument,
            'branch' => $this->branch,
            'pos' => $this->pos,
            'payment_method' => $this->paymentMethod,
            'discount' => $this->discount,
            'amount' => $this->amount,
        ];

        $rules = [
            'ticket' => ['required', 'string', 'unique:bei_invoices,bei_ticket'],
            'type_document' => ['required', 'string', new RuleCheckTypeDocuments()],
            'branch' => ['required', 'integer','min:0','max:20'],
            'pos' => ['nullable', 'integer','min:0','max:100'],
            'payment_method' => ['required', 'string', new CheckParametricRule(ParametricType::METODOS_DE_PAGO, true)],
            'discount' => ['nullable', 'numeric','min:0.00','max:10000'],
            'amount' => ['required', 'numeric','min:0.01','max:10000'],
        ];

        $messages = [
            //ticket
            'ticket.required' => 'El ticket es obligatorio.',
            'ticket.string' => 'El campo ticket debe ser texto válido.',
            'ticket.unique' => 'El campo ticket ya registrado.',

            // type_document
            'type_document.required' => 'El tipo de documento es obligatorio.',
            'type_document.string' => 'El campo tipo de documento debe ser texto válido.',

            // branch
            'branch.required' => 'La sucursal es obligatoria.',
            'branch.integer' => 'La sucursal debe ser un número entero.',
            'branch.min'     => 'La sucursal debe ser como mínimo 0.',
            'branch.max'     => 'La sucursal no puede ser mayor a 20.',

            // pos
            'pos.integer' => 'El punto de venta debe ser un número entero.',
            'pos.min'     => 'El punto de venta debe ser como mínimo 0.',
            'pos.max'     => 'El punto de venta no puede ser mayor a 100.',

            // payment_method
            'payment_method.required' => 'El método de pago es obligatorio.',
            'payment_method.string' => 'El método de pago debe ser texto válido.',

            // discount
            'discount.required' => 'El descuento es obligatorio.',
            'discount.numeric'  => 'El descuento debe ser un número válido.',
            'discount.min'      => 'El descuento debe ser mayor o igual a 0.01.',
            'discount.max'      => 'El descuento no puede ser mayor a 10000.',

            // amount
            'amount.required' => 'El monto es obligatorio.',
            'amount.numeric'  => 'El monto debe ser un número válido.',
            'amount.min'      => 'El monto debe ser mayor o igual a 0.01.',
            'amount.max'      => 'El monto no puede ser mayor a 10000.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' | ', $errors);
            throw new EmizorApiDefaultsValidationException("Errores de validación en la creación de la factura: " . $errorMessage);
        }
    }


    public function toArray(): array
    {
        return [
            'bei_ticket' => $this->ticket,
            'bei_sector_document_id' => $this->typeDocument,
            'bei_amount_total' => $this->amount,
            'bei_pos_code' => $this->pos,
            'bei_branch_code' => $this->branch,
            'bei_payment_method' => $this->paymentMethod,
            'bei_client' => $this->client,
            'bei_details' => $this->details,
        ];
    }
}
