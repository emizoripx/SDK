<?php

namespace Emizor\SDK\DTO;

class RegisterDTO
{
    public string $host;
    public string $clientId;
    public string $clientSecret;
    public bool $demo;

    public function __construct(string $host, string $clientId, string $clientSecret, bool $demo = true)
    {
        $this->host = $host;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->demo = $demo;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['host'] ?? '',
            $data['client_id'] ?? '',
            $data['client_secret'] ?? '',
            $data['demo'] ?? true
        );
    }
}
