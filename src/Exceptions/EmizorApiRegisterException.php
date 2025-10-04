<?php

namespace Emizor\SDK\Exceptions;

class EmizorApiRegisterException extends EmizorSdkException
{
    public function __construct(string $message = "Registration error occurred", int $code = 1004, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
