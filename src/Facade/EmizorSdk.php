<?php

namespace Emizor\SDK\Facade;

use Illuminate\Support\Facades\Facade;

class EmizorSdk extends Facade
{
    protected static function getFacadeAccessor()
    {
        // Nombre del binding que registrarás en el ServiceProvider
        return 'emizorsdk';

    }
}
