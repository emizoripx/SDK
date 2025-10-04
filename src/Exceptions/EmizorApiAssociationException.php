<?php

namespace Emizor\SDK\Exceptions;

class EmizorApiAssociationException extends EmizorSdkException
{
    public function __construct(string $message = "Association error occurred", int $code = 1010, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}