<?php

namespace Emizor\SDK\Repositories;

use Emizor\SDK\DTO\ParametricDTO;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiGlobalParametric;
use Emizor\SDK\Models\BeiSpecificParametric;

class ParametricRepository
{
    public function store( $type, array $data, string $accountId): void
    {

        if ( in_array(ParametricType::from($type) ,[ParametricType::ACTIVIDADES, ParametricType::PRODUCTOS_SIN, ParametricType::LEYENDAS])) {
            foreach ($data as $item) {
                BeiSpecificParametric::updateOrCreate(
                    [
                        "bei_code" =>(string) $item['codigo'],
                        "bei_description" => $item['descripcion'],
                        "bei_activity_code" => isset($item['codigoActividad']) ? (string)$item['codigoActividad']: null,
                        "bei_type" => $type,
                        "bei_account_id" => $accountId
                    ]
                );
            }
        } else {
            foreach ($data as $item) {
                BeiGlobalParametric::updateOrCreate(
                    [
                        "bei_code" => $item['codigo'],
                        "bei_description" => $item['descripcion'],
                        "bei_type" => $type

                    ]
                );
            }
        }
    }


    public function list( $type, $accountId=null):array
    {
        if ( in_array( ParametricType::from($type), [ParametricType::ACTIVIDADES, ParametricType::PRODUCTOS_SIN, ParametricType::LEYENDAS])) {
            return BeiSpecificParametric::where('bei_account_id', $accountId)->where("bei_type", $type)->get()?->map(fn ($m) => ParametricDTO::fromSpecificModel($m)->toArray())->toArray();
        } else {
            return BeiGlobalParametric::where("bei_type", $type)->get()?->map(fn ($m) => ParametricDTO::fromGlobalModel($m)->toArray())->toArray();
        }

    }


}
