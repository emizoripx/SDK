<?php
namespace Emizor\SDK\Exceptions;

class EmizorApiConnectionTimeoutException extends EmizorSdkException
{
    public function __construct(string $message = "Connection timeout occurred", int $code = 1002, ?EmizorSdkException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
