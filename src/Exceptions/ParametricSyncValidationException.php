<?php

namespace Emizor\SDK\Exceptions;

class ParametricSyncValidationException extends EmizorSdkException
{
    public function __construct(string $message = "Parametric sync validation error", int $code = 1006, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
