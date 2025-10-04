<?php

namespace Emizor\SDK\Contracts;

use Closure;
use Emizor\SDK\Models\BeiAccount;

interface RegisterContract
{
    public function register(Closure $callback): BeiAccount;
}