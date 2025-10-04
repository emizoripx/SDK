<?php

namespace Emizor\SDK\Exceptions;

class EmizorApiSyncException extends EmizorSdkException
{
    public function __construct(string $message = "Sync error occurred", int $code = 1008, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}