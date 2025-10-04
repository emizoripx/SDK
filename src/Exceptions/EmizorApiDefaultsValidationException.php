<?php

namespace Emizor\SDK\Exceptions;

class EmizorApiDefaultsValidationException extends EmizorSdkException
{
    public function __construct(string $message = "Defaults validation error", int $code = 1003, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
