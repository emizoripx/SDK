<?php

namespace Emizor\SDK\Facade;

use Emizor\SDK\Enums\ParametricType;
use Illuminate\Support\Facades\Facade;

class EmizorSdk extends Facade
{

    protected static function getFacadeAccessor()
    {
        return 'emizorsdk';
    }
    public static function register($callback)
    {
        return app('emizorsdk')->register($callback);
    }

    public static function for($owner)
    {
        return app('emizorsdk-manager')->for($owner);
    }

}
