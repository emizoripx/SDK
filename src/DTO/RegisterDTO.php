<?php

namespace Emizor\SDK\DTO;

use Emizor\SDK\Exceptions\RegisterValidationException;
use Emizor\SDK\Models\BeiAccount;
use Illuminate\Support\Facades\Validator;

class RegisterDTO
{
    public function __construct(
        public string $host,
        public string $clientId,
        public string $clientSecret,
        public ?string $ownerId = null,
        public ?string $ownerType = null
    )
    {
        $this->validate();
    }

    public function validate()
    {
        // Check if client_id already exists
        if (BeiAccount::where('bei_client_id', $this->clientId)->exists()) {
            throw new RegisterValidationException('El client_id ya está registrado.');
        }

        $data = [
            'host' => $this->host,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'owner_id' => $this->ownerId,
            'owner_type' => $this->ownerType,
        ];

        $rules = [
            'host' => ['required', 'string', 'in:PILOTO,PRODUCTION'],
            'client_id' => ['required', 'string', 'min:4', 'max:8'],
            'client_secret' => ['required', 'string', 'min:10'],
            'owner_id' => ['nullable', 'string'],
            'owner_type' => ['nullable', 'string', 'in:' . implode(',', array_keys(config('emizor_sdk.owners', [])))],
        ];

        $messages = [
            'host.required' => 'El host es obligatorio.',
            'host.in' => 'El host debe ser PILOTO o PRODUCTION.',
            'client_id.required' => 'El client_id es obligatorio.',
            'client_id.min' => 'El client_id debe tener al menos 4 caracteres.',
            'client_id.max' => 'El client_id no puede tener más de 8 caracteres.',
            'client_secret.required' => 'El client_secret es obligatorio.',
            'client_secret.min' => 'El client_secret debe tener al menos 10 caracteres.',
        ];

        $validator = Validator::make($data, $rules, $messages);

        $validator->after(function ($validator) {
            if (($this->ownerId && !$this->ownerType) || (!$this->ownerId && $this->ownerType)) {
                $validator->errors()->add('owner', 'Ambos owner_id y owner_type deben estar presentes o ausentes.');
            }
        });

        if ($validator->fails()) {
            $errors = $validator->errors()->all();
            $errorMessage = implode(' | ', $errors);
            throw new RegisterValidationException($errorMessage);
        }
    }




    public static function fromArray(array $data): self
    {
        return new self(
            $data['host'] ?? '',
            $data['client_id'] ?? '',
            $data['client_secret'] ?? '',
            $data['owner_id'] ?? null,
            $data['owner_type'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'host' => $this->host,
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'owner_id' => $this->ownerId,
            'owner_type' => $this->ownerType,
        ];
    }
}
