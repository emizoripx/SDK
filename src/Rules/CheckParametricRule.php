<?php

namespace Emizor\SDK\Rules;

use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiGlobalParametric;
use Emizor\SDK\Models\BeiSpecificParametric;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class CheckParametricRule implements Rule
{
    protected ParametricType $parametric;
    public function __construct(ParametricType $parametric, $global = true)
    {
        $this->parametric = $parametric;
        $this->value = null;
        $this->attr = null;
        $this->global = $global;
    }
    public function passes($attribute, $value): bool
    {
        $this->attr = $attribute;
        $this->value = $value;
        if ($this->global)
            return BeiGlobalParametric::where("bei_type",$this->parametric->value)->where('bei_code', $value)->exists();
        else
            return BeiSpecificParametric::where("bei_type",$this->parametric->value)->where('bei_code', $value)->exists();

    }

    public function message(): string
    {
        return "El ".$this->attr. ": ".$this->value." no es existe.";
    }
}
