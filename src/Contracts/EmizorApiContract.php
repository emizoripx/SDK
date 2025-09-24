<?php
namespace Emizor\SDK\Contracts;

use Emizor\SDK\DTO\RegisterDTO as RegisterCredentialsDTO;
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
      * Construct the API bound to an account (or null for unauth operations). 
      * @param string|null $accountId 
      */ 
    public function __construct(?string $accountId = null); 
}