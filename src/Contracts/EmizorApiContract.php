<?php
namespace Emizor\SDK\Contracts;

use Closure;
use Emizor\SDK\DTO\RegisterDTO as RegisterCredentialsDTO;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Exceptions\EmizorApiConnectionTimeoutException;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;

interface EmizorApiContract {

      /** * Register a new account with credentials (returns internal account id).
       * - Validates client_id/secret and host.
       * - Obtains token and stores association with account.
       * @param Closure $callback
       * @return string $accountId
       * @throws EmizorApiRegisterException
       * @throws EmizorApiConnectionTimeoutException
       */
     public function register(Closure $callback): string;


    /**
     * List parametrics types
     * @return array
     */
    public function listParametricsTypes():array;

    /**
     * Sync parametrics by type, according to account will decide if specific or global
     * @param ParametricType $type
     */
     public function syncParametrics(array $parametrics):void;

     public function sync(array $parametrics):void;

     /**
      * Get parametrics by type, according to account will decide if specific or global
      * @param string $type
      */
     public function getParametric($type):array;

    /**
     * Define defaults for this account, using a closure-style builder.
     *
     * @param Closure $callback
     * @return self
     */
    public function setDefaults(Closure $callback): self;

    /**
     * Get the current defaults applied for this account.
     *
     * @return array
     */
    public function getDefaults(): array;

    /**
     * @param array $products
     * @return void
     */
    public function homologateProduct(array $products):void;

    /**
     * @return array $products
     */
    public function homologateProductList():array;

    /**
     * Issue and invoice
     *
     * @param Closure $callback
     * @param string $ticket
     * @return self
     */
    public function issueInvoice(Closure $callback, string $ticket):self;


    /**
     * @param $nit
     * @return array
     */
    public function validateNit($nit): array;

    /**
     * @param string $ticket
     * @param int $revocationReasonCode
     * @return void
     */
    public function revocateInvoice(string $ticket, int $revocationReasonCode):void;
}
