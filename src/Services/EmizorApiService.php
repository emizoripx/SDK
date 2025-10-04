<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Enums\InvoiceType;

class EmizorApiService implements EmizorApiHttpContract
{

    private HttpClientInterface $http;

    public function __construct(HttpClientInterface $http)
    {
        $this->http = $http;
    }

    private function resolveHost(string $host): string
    {
        return match ($host) {
            'PILOTO' => 'https://sinfel.emizor.com',
            'PRODUCTION' => 'https://fel.emizor.com',
            default => $host, // Allow custom URLs if needed
        };
    }

    public function generateToken(string $host, string $clientId, string $clientSecret): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host));

        return $configuredHttp->post('/oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]);
    }

    public function checkNit(string $host, string $token, $nit): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        return $configuredHttp->get("/api/v1/sucursales/0/validate-nit/$nit");
    }

    public function sendInvoice(string $host, string $token, array $data): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/sucursales/" . $data['codigoSucursal'] . "/facturas/" . InvoiceType::getName($data["codigoDocumentoSector"]);
        info("send to " . $endpoint);
        return $configuredHttp->post($endpoint, $data);
    }

    public function getDetailInvoice(string $host, string $token, string $ticket): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/facturas/$ticket";
        info("send to " . $endpoint);
        return $configuredHttp->get($endpoint, ["unique_code" => 'true']);
    }

    public function revocateInvoice(string $host, string $token, string $ticket, int $revocationReasonCode): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/facturas/$ticket/anular";
        info("send to " . $endpoint);
        return $configuredHttp->delete($endpoint, ["codigoMotivoAnulacion" => $revocationReasonCode]);
    }

    public function getParametrics(string $host, string $token, string $type): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/parametricas/" . $type;
        return $configuredHttp->get($endpoint);
    }

}
