<?php
namespace Emizor\SDK\Contracts;

use Emizor\SDK\DTO\RegisterDTO as RegisterCredentialsDTO;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Exceptions\EmizorApiConnectionTimeoutException;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;

interface EmizorApiContract {

     /** * Register a new account with credentials (returns internal account id).
      * - Validates client_id/secret and host.
      * - Obtains token and stores association with account.
      * @param RegisterCredentialsDTO $credentials
      * @return string $accountId
      * @throws EmizorApiRegisterException
      * @throws EmizorApiConnectionTimeoutException
      */
    public function register(RegisterCredentialsDTO $credentials): string;


    /**
     * Sync parametrics by type, according to account will decide if specific or global
     * @param ParametricType $type
     */
    public function sync(array $parametrics):void;

    /**
     * Get parametrics by type, according to account will decide if specific or global
     * @param ParametricType $type
     */
    public function getParametric(ParametricType $type):array;
}
