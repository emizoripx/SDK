<?php

namespace Emizor\SDK\Builders;

use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\Enums\EnvironmentType;

class RegisterBuilder
{
    protected string $clientId;
    protected string $clientSecret;
    protected EnvironmentType $environment;

    public function setClientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function setClientSecret(string $clientSecret): self
    {
        $this->clientSecret = $clientSecret;
        return $this;
    }

    public function usePilotoEnvironment(): self
    {
        $this->environment = EnvironmentType::PILOTO;
        return $this;
    }

    public function useProductionEnvironment(): self
    {
        $this->environment = EnvironmentType::PRODUCTION;
        return $this;
    }

    public function build(): RegisterDTO
    {
        return new RegisterDTO(
            $this->environment->value,
            $this->clientId,
            $this->clientSecret
        );
    }
}