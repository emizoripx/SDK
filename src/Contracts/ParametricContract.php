<?php
namespace Emizor\SDK\Contracts;

use Emizor\SDK\DTO\ParametricDTO;
use Emizor\SDK\Enums\ParametricType;

interface ParametricContract
{
/**
* Sincroniza desde API y guarda en DB.
*/
public function sync( $type, string $accountId): void;

/**
* Obtiene paramétricas ya sincronizadas desde DB.
*
* @return ParametricDTO[]
*/
public function get( $type, string $accountId): array;
}
