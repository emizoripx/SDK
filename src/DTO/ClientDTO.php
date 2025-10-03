<?php

namespace Emizor\SDK\DTO;

use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Rules\CheckParametricRule;
use Illuminate\Support\Facades\Validator;
use Emizor\SDK\Exceptions\EmizorApiDefaultsValidationException;
use Emizor\SDK\Enums\EmizorApiDocumentNumberType;

final class ClientDTO
{
    public function __construct(
        private string $client_code,
        private string $client_document_number,
        private string $client_business_name,
        private ?string $client_complement = null,
        private string $client_document_number_type = "ci",
    ) {
        // generate uuid short if not set
        if (empty($this->client_code)) {
            $this->client_code = substr(str_replace('-', '', uuid_create()), 0, 10);
        }

        $this->validate();
    }

    private function validate(): void
    {

        $data = [
            'client_code'                 => $this->client_code,
            'client_document_number'      => $this->client_document_number,
            'client_business_name'        => $this->client_business_name,
            'client_complement'           => $this->client_complement,
            'client_document_number_type' => $this->client_document_number_type,
        ];

        $rules = [
            'client_code'                 => ['required', 'string', 'max:50'],
            'client_document_number'      => ['required', 'string'],
            'client_business_name'        => ['required', 'string'],
            'client_complement'           => ['nullable', 'string'],
            'client_document_number_type' => ["required", "string", new CheckParametricRule(ParametricType::TIPOS_DOCUMENTO_IDENTIDAD)],
        ];
        $messages = [
            'client_code.required' => 'El "client_code" es obligatorio.',
            'client_code.string'   => 'El "client_code" debe ser un texto.',
            'client_code.max'      => 'El "client_code" no debe exceder los 10 caracteres.',

            'client_document_number.required' => 'El "client_document_number" es obligatorio.',
            'client_document_number.string'   => 'El "client_document_number" debe ser un texto.',

            'client_business_name.required' => 'El "client_business_name" es obligatoria.',
            'client_business_name.string'   => 'El "client_business_name" debe ser un texto.',

            'client_complement.string' => 'El "client_complement" debe ser un texto.',

            'client_document_number_type.required' => 'El tipo de documento es obligatorio.',
            'client_document_number_type.string'   => 'El tipo de documento debe ser un texto.',
            'client_document_number_type.in'       => 'El tipo de documento debe ser CI, NIT o PASSPORT.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        if ($validator->fails()) {
            throw new EmizorApiDefaultsValidationException(
                "Errores en cliente: " . implode(' | ', $validator->errors()->all())
            );
        }
    }

    public function toArray(): array
    {
        return [
            'client_code'                 => $this->client_code,
            'client_document_number'      => $this->client_document_number,
            'client_business_name'        => $this->client_business_name,
            'client_complement'           => $this->client_complement,
            'client_document_number_type' => $this->client_document_number_type,
        ];
    }
}
