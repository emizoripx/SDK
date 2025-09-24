<?php

namespace Emizor\SDK\Contracts;

interface TokenContract
{

    /**
     * Generate token using credentials, CLIENT_ID, CLIENT_SECRET
     *
     * @param string $clientId
     * @param string $clientSecret
     * @return array
     */

    public function generate( string $clientId, string $clientSecret): array;

    /**
     * Construct the service generate client base uri.
     *
     * @param HttpClientInterface $http
     */

    public function __construct(HttpClientInterface $http);

}
