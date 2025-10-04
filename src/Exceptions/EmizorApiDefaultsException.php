<?php

namespace Emizor\SDK\Exceptions;

class EmizorApiDefaultsException extends EmizorSdkException
{
    public function __construct(string $message = "Defaults error occurred", int $code = 1009, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}