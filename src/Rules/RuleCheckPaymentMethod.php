<?php

namespace Emizor\SDK\Rules;

use Emizor\SDK\Models\BeiGlobalParametric;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RuleCheckPaymentMethod implements Rule
{
    public function passes($attribute, $value): bool
    {
        return BeiGlobalParametric::where("bei_type","payment_methods")->where('bei_code', $value)->exists();
    }

    public function message(): string
    {
        return "El método de pago no es válido.";
    }
}
