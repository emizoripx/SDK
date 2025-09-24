<?php

namespace Emizor\SDK\Contracts;

interface TokenContract
{
    /**
     * Generate token using credentials, specifying the host dynamically.
     *
     * @param string $host The API host
     * @param string $clientId
     * @param string $clientSecret
     * @return array
     */
    public function generate(string $host, string $clientId, string $clientSecret): array;
}
