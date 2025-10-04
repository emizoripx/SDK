<?php

namespace Emizor\SDK\Exceptions;

class RegisterValidationException extends EmizorSdkException
{
    public function __construct(string $message = "Register validation error", int $code = 1007, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
