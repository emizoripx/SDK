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

    public function setHost(string $host): static
    {
        $this->http = $this->http->withBaseUri($host);
        return $this;
    }

    public function setToken(string $token): static
    {
        $this->http = $this->http->withToken($token);
        return $this;
    }

    public function generateToken( string $clientId, string $clientSecret ) : array
    {

        return  $this->http->post('/oauth/token', [
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials',
        ]);

    }

    public function checkNit($nit):array
    {
        return $this->http->get("/api/v1/sucursales/0/validate-nit/$nit");
    }

    public function sendInvoice(array $data):array
    {
        $endpoint = "/api/v1/sucursales/" . $data['codigoSucursal'] . "/facturas/" . InvoiceType::getName($data["codigoDocumentoSector"]);
        info("send to " . $endpoint);
        return $this->http->post( $endpoint , $data);
    }

    public function getDetailInvoice(string $ticket):array
    {
        $endpoint = "/api/v1/facturas/$ticket";
        info("send to " . $endpoint);
        return $this->http->get( $endpoint, ["unique_code" => 'true']);
    }

    public function revocateInvoice(string $ticket, int $revocationReasonCode):array
    {
        $endpoint = "/api/v1/facturas/$ticket/anular";
        info("send to " . $endpoint);
        return $this->http->delete( $endpoint ,array("codigoMotivoAnulacion" => $revocationReasonCode));
    }

}
