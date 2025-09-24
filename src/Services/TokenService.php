<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Contracts\TokenContract;
use Carbon\Carbon;
use Exception;


class TokenService implements TokenContract
{

    protected HttpClientInterface $http;

    public function __construct(HttpClientInterface $http)
    {
        $this->http = $http;
    }

    /**
     * Establece el host dinámicamente antes de generar el token
     */
    public function setHost(string $host): void
    {
        $this->host = $host;
        $this->http->setBaseUri($host);
    }

    public function generate(string $clientId, string $clientSecret) : array
    {

        $response = $this->http->post('/oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]);


        if (!isset($response['access_token'])) {
            throw new Exception("Error al generar token: respuesta inválida");
        }

        return [
            'token' => $response['access_token'] ?? null,
            'expires_at' => Carbon::now()->addSeconds($response['expires_in'] ?? 3600),
        ];
    }
}
