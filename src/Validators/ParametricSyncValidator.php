<?php

namespace Emizor\SDK\Validators;

use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Exceptions\ParametricSyncValidationException;


class ParametricSyncValidator
{
    public function validate(array $parametrics_data): void
    {
        foreach($parametrics_data as $data) {
            if (!in_array(ParametricType::from($data), ParametricType::cases())) {
                throw new ParametricSyncValidationException("Paramétric type should be valid [" . $data. "]");
            }
        }

    }
}
