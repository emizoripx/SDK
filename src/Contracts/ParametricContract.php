<?php
namespace Emizor\SDK\Contracts;

use Emizor\SDK\DTO\ParametricDTO;
use Emizor\SDK\Enums\ParametricType;

interface ParametricContract
{

/**
* Sync from API and save in DB
*/
public function sync(string $host, string $token, $type, string $accountId): void;

/**
* Obtiene paramétricas ya sincronizadas desde DB.
*
* @return array
*/
public function get( $type, string $accountId): array;
}
