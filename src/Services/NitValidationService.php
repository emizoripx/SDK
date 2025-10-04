<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\NitValidationContract;

class NitValidationService implements NitValidationContract
{

    protected EmizorApiHttpContract $emizorApiHttpService;

    public function __construct(EmizorApiHttpContract $emizorApiHttpService)
    {
        $this->emizorApiHttpService = $emizorApiHttpService;
    }

    public function validate(string $host, string $token, string $nit ) : array
    {

        return $this->emizorApiHttpService->checkNit($host, $token, $nit);

    }
}
