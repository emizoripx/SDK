<?php

namespace Emizor\SDK\Exceptions;

class EmizorApiAccountException extends EmizorSdkException
{
    public function __construct(string $message = "Account error occurred", int $code = 1001, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
