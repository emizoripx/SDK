<?php

namespace Emizor\SDK\Contracts;

interface TokenContract
{

    public function setHost(string $host):static;
    /**
     * Generate token using credentials, specifying the host dynamically.
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return array
     */
    public function generate(string $clientId, string $clientSecret): array;
}
