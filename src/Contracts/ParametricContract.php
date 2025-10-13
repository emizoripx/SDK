<?php
namespace Emizor\SDK\Contracts;

use Emizor\SDK\DTO\ParametricDTO;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiAccount;

interface ParametricContract
{

    /**
     * Sync from API and save in DB
     * @param BeiAccount $beiAccount
     * @param string $type
     */
    public function syncAndSaveParametric(BeiAccount $beiAccount, string $type): void;

    /**
     * Obtiene paramétricas ya sincronizadas desde DB.
     *
     * @param BeiAccount $beiAccount
     * @param string $type
     * @return array
     */
    public function getParametric(BeiAccount $beiAccount, string $type): array;


    /**
     * Returns list of types of parametrics
     * @return array
     */
    public function listParametricTypes():array;
}
