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

    private function isSuccesfulResponse($statusCode): bool
    {
        return in_array($statusCode, [200, 201]);
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

        $configuredHttp->post('/oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]);

        return $configuredHttp->getResponse();

    }

    public function checkNit(string $host, string $token, $nit): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $configuredHttp->get("/api/v1/sucursales/0/validate-nit/$nit");

        return $configuredHttp->getResponse();
    }

    public function sendInvoice(string $host, string $token, array $data): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/sucursales/" . $data['codigoSucursal'] . "/facturas/" . InvoiceType::getName($data["codigoDocumentoSector"]);
        info("send to " . $endpoint);
        $configuredHttp->post($endpoint, $data);
        return $configuredHttp->getResponse();
    }

    public function getDetailInvoice(string $host, string $token, string $ticket): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/facturas/$ticket";
        info("send to " . $endpoint);
        $configuredHttp->get($endpoint, ["unique_code" => 'true']);
        return $configuredHttp->getResponse();
    }

    public function revocateInvoice(string $host, string $token, string $ticket, int $revocationReasonCode): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/facturas/$ticket/anular";
        info("send to " . $endpoint);
        $configuredHttp->delete($endpoint, ["codigoMotivoAnulacion" => $revocationReasonCode]);
        return $configuredHttp->getResponse();
    }

    public function getParametrics(string $host, string $token, string $type): array
    {
        $configuredHttp = $this->http->withBaseUri($this->resolveHost($host))->withToken($token);

        $endpoint = "/api/v1/parametricas/" . $type;
        $configuredHttp->get($endpoint);
        return $configuredHttp->getResponse();
    }

}
