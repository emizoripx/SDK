<?php

namespace Emizor\SDK\Validators;

use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\Exceptions\RegisterValidationException;
use Emizor\SDK\Models\BeiAccount;

class RegisterValidator
{
    public function validate(RegisterDTO $dto): void
    {
        if (empty($dto->host) || empty($dto->clientId) || empty($dto->clientSecret)) {
            throw new RegisterValidationException("Host, Client ID y Client Secret son requeridos.");
        }

        if (!filter_var($dto->host, FILTER_VALIDATE_URL)) {
            throw new RegisterValidationException("El host no tiene un formato válido.");
        }

        if (BeiAccount::where('bei_client_id', $dto->clientId)->exists()) {
            throw new RegisterValidationException("El Client ID ya está registrado.");
        }
    }
}
