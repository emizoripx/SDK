<?php

namespace Emizor\SDK\Contracts;

interface NitValidationContract
{

    /**
     * Generate token using credentials, specifying the host dynamically.
     *
     * @param string $host
     * @param string $token
     * @param string $nit
     * @return array
     */
    public function validate(string $host, string $token, string $nit): array;
}
