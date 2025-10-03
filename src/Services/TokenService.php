<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\TokenContract;
use Carbon\Carbon;
use Exception;


class TokenService implements TokenContract
{

    protected EmizorApiHttpContract $emizorApiHttpService;

    public function __construct(EmizorApiHttpContract $emizorApiHttpService)
    {
        $this->emizorApiHttpService = $emizorApiHttpService;
    }

    public function generate(string $host, string $clientId, string $clientSecret ) : array
    {

        $response = $this->emizorApiHttpService
            ->setHost($host)
            ->generateToken(
                $clientId,
                $clientSecret
            );


        if (!isset($response['access_token'])) {
            throw new Exception("Error al generar token: respuesta inválida");
        }

        return [
            'token' => $response['access_token'] ?? null,
            'expires_at' => Carbon::now()->addSeconds($response['expires_in'] ?? 3600),
        ];
    }
}
