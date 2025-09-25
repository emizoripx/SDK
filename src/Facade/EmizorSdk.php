<?php

namespace Emizor\SDK\Facade;

use Illuminate\Support\Facades\Facade;

class EmizorSdk extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'emizorsdk';
    }
    public static function register($dto)
    {
        return app('emizorsdk')->register($dto);
    }

    public static function withAccount(string $accountId)
    {
        return app('emizorsdk', ['accountId' => $accountId]);
    }


}
