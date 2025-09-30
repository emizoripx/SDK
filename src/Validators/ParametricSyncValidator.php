<?php

namespace Emizor\SDK\Validators;

use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Exceptions\ParametricSyncValidationException;


class ParametricSyncValidator
{
    public function validate(array $parametrics_data): void
    {

        foreach($parametrics_data as $data) {
            try {

                $parmString = ParametricType::from($data);

            } catch(\Throwable $th) {
                throw new ParametricSyncValidationException("Inexistent parametric [".$data."], refer to parametric list.");
            }


            if (!in_array($parmString, ParametricType::cases())) {
                throw new ParametricSyncValidationException("Paramétric type should be valid [" . $data. "]");
            }
        }

    }
}
