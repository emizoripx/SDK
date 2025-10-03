<?php

namespace Emizor\SDK\DTO;

use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiGlobalParametric;
use Emizor\SDK\Models\BeiSpecificParametric;

class ParametricDTO
{
    public function __construct(
        public string $code,
        public string $description,
        public ParametricType $type,
        public ?string $activityCode = null,
        public ?string $accountId = null
    ) {}


    /**
     * Build for global parametric
     */
    public static function fromGlobalModel(BeiGlobalParametric $model): self
    {
        return new self(
            code: $model->bei_code,
            description: $model->bei_description,
            type: ParametricType::from($model->bei_type),
        );
    }

    /**
     * Build for specific parametric
     */
    public static function fromSpecificModel(BeiSpecificParametric $model): self
    {
        return new self(
            code: $model->bei_code,
            description: $model->bei_description,
            type: ParametricType::from($model->bei_type),
            activityCode: $model->bei_activity_code,
        );
    }

    /**
     * Convert to array each DTO parametric
     */
    public function toArray(): array
    {
        $array = [
            'bei_type' => $this->type->value,
            'bei_code' => $this->code,
            'bei_description' => $this->description,
        ];

        if($this->type->value == ParametricType::PRODUCTOS_SIN->value) {
            $array['bei_activity_code'] = $this->activityCode;
        }

        return $array;
    }
}
