<?php

namespace Emizor\SDK\Exceptions;

class EmizorApiTokenException extends EmizorSdkException
{
    public function __construct(string $message = "Token error occurred", int $code = 1005, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
