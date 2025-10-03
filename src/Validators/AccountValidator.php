<?php

namespace Emizor\SDK\Validators;

use Emizor\SDK\Exceptions\EmizorApiAccountException;
use Emizor\SDK\Exceptions\EmizorApiTokenException;
use Emizor\SDK\Exceptions\ParametricSyncValidationException;
use Emizor\SDK\Models\BeiAccount;
use DateTimeImmutable;
use DateTimeZone;
use Emizor\SDK\Repositories\ParametricRepository;
use Emizor\SDK\Services\TokenManager;
use Lcobucci\JWT\Token\Parser;
use Lcobucci\JWT\Encoding\JoseEncoder;

class AccountValidator
{
    protected ParametricRepository $repository ;
    private TokenManager $tokenManager;

    public function __construct(ParametricRepository $repository, TokenManager $tokenManager)
    {
        $this->repository = $repository;
        $this->tokenManager = $tokenManager;
    }

    public function validate(string $accountId): BeiAccount | null
    {
        if (empty($accountId))
            throw new EmizorApiAccountException("AccountId is required");

        $account = BeiAccount::find($accountId);

        if (empty($account))
            throw new EmizorApiAccountException("Account not found");

        if (empty($account->bei_token)) {
            $this->tokenManager->generateAndSaveToken($account);
        }

        $this->validateToken($account->bei_token);

        return $account;

    }

    public function validateToken($token):void
    {
        try {
            $parser = new Parser(new JoseEncoder());
            $decodedToken = $parser->parse($token);
            // Get expiration date from token
            $expirationDate = $decodedToken->claims()->get('exp');
            $origin = new DateTimeImmutable("now",  new DateTimeZone("America/La_Paz"));
            $interval = $origin->diff($expirationDate);

            // check due date token
            if ($interval->format('%a') < 1)
                throw new EmizorApiTokenException("Token Expired");
        } catch (\Throwable $e) {

            info("Error: " . $e->getMessage() . " Line: " . $e->getLine() . " File: " . $e->getFile());
            throw new EmizorApiTokenException("Token Invalid");
        }

    }

}
