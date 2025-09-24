<?php

namespace Emizor\SDK;

use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\Validators\RegisterValidator;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;
use Emizor\SDK\Contracts\EmizorApiContract;

class EmizorApi implements EmizorApiContract
{
    protected RegisterValidator $validator;
    protected AccountRepository $repository;

    public function __construct(?string $accountId = null, RegisterValidator $validator = null, AccountRepository $repository = null)
    {
        $this->accountId = $accountId;
        $this->validator = $validator ?? new RegisterValidator();
        $this->repository = $repository ?? new AccountRepository();
    }
    

    public function register(RegisterDTO $dto): string
    {
        // 1. Validar
        $this->validator->validate($dto);

        try {
            // 2. Guardar en BD
            $account = $this->repository->create([
                'id'                 => \Str::uuid()->toString(),
                'bei_enable'         => true,
                'bei_verified_setup' => true,
                'bei_client_id'      => $dto->clientId,
                'bei_client_secret'  => $dto->clientSecret,
                'bei_host'           => $dto->host,
                'bei_demo'           => $dto->demo,
            ]);

            return $account->id;
        } catch (\Exception $e) {
            throw new EmizorApiRegisterException("Error al registrar la cuenta: " . $e->getMessage());
        }
    }
}
