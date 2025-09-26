<?php
namespace Emizor\SDK\Contracts;

use Emizor\SDK\DTO\ParametricDTO;
use Emizor\SDK\Enums\ParametricType;

interface ParametricContract
{

/**
* Set Host for getting parametrics
*/
public function setHost( string $host ): static;

/**
* Set Token for getting parametrics
*/
public function setToken( string $token ): static;

/**
* Sync from API and save in DB
*/
public function sync( $type, string $accountId): void;

/**
* Obtiene paramétricas ya sincronizadas desde DB.
*
* @return array
*/
public function get( $type, string $accountId): array;
}
