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

    /**
     * @param string $host
     * @return $this
     */
    public function setHost(string $host): static;

    /**
     * @param string $token
     * @return $this
     */
    public function setToken(string $token): static;


    public function generateToken(string $clientId, string $clientSecret): array;
    /**
     * @param $nit
     * @return array
     */
    public function checkNit($nit): array;

    /**
     * @param array $data
     * @return array
     */
    public function sendInvoice(array $data): array;

    /**
     * @param string $ticket
     * @return array
     */
    public function getDetailInvoice(string $ticket): array;

    /**
     * @param string $ticket
     * @param int $revocationReasonCode
     * @return array
     */
    public function revocateInvoice(string $ticket, int $revocationReasonCode): array;
}
