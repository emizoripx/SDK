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
     * Construir desde un array (p.e. respuesta de API externa).
     */
    public static function fromArray(array $data, ParametricType $type, ?string $accountId = null): self
    {
        return new self(
            code: $data['codigo'] ?? '',
            description: $data['descripcion'] ?? '',
            type: $type,
            activityCode: $data['actividadCodigo'] ?? null,
            accountId: $accountId
        );
    }

    /**
     * Construir desde un modelo global.
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
     * Construir desde un modelo específico.
     */
    public static function fromSpecificModel(BeiSpecificParametric $model): self
    {
        return new self(
            code: $model->bei_code,
            description: $model->bei_description,
            type: ParametricType::from($model->bei_type),
            activityCode: $model->bei_activity_code,
            accountId: $model->bei_account_id
        );
    }

    /**
     * Convertir DTO a array (para exponer en contract / API).
     */
    public function toArray(): array
    {
        return [
            'code' => $this->code,
            'description' => $this->description,
            'type' => $this->type->value,
            'activity_code' => $this->activityCode,
            'account_id' => $this->accountId,
        ];
    }
}
