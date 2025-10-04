<?php
namespace Emizor\SDK\Contracts;

/**
 *
 */
interface EmizorApiHttpContract
{
    /**
     * @param HttpClientInterface $http
     */
    public function __construct(HttpClientInterface $http);

    public function generateToken(string $host, string $clientId, string $clientSecret): array;

    /**
     * @param string $host
     * @param string $token
     * @param $nit
     * @return array
     */
    public function checkNit(string $host, string $token, $nit): array;

    /**
     * @param string $host
     * @param string $token
     * @param array $data
     * @return array
     */
    public function sendInvoice(string $host, string $token, array $data): array;

    /**
     * @param string $host
     * @param string $token
     * @param string $ticket
     * @return array
     */
    public function getDetailInvoice(string $host, string $token, string $ticket): array;

    /**
     * @param string $host
     * @param string $token
     * @param string $ticket
     * @param int $revocationReasonCode
     * @return array
     */
    public function revocateInvoice(string $host, string $token, string $ticket, int $revocationReasonCode): array;

    /**
     * @param string $host
     * @param string $token
     * @param string $type
     * @return array
     */
    public function getParametrics(string $host, string $token, string $type): array;
}
