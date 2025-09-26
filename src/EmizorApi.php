<?php

namespace Emizor\SDK;

use Emizor\SDK\Builders\DefaultsBuilder;
use Emizor\SDK\Contracts\EmizorApiContract;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\Enums\ParametricType;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Repositories\ParametricRepository;
use Emizor\SDK\Services\TokenService;
use Emizor\SDK\Services\ParametricService;
use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Validators\AccountValidator;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;
use Emizor\SDK\Validators\ParametricSyncValidator;
use \Closure;

class EmizorApi implements EmizorApiContract
{
    private ?string $accountId;
    private ?BeiAccount $account;
    private HttpClientInterface $http;
    private ParametricContract $parametricService;
    private TokenService $tokenService;
    private AccountRepository $repository;
    private AccountValidator $accountValidator;
    private ParametricSyncValidator $parametricValidator;

    public function __construct(
        HttpClientInterface $http,
        AccountRepository $repository,
        TokenService $tokenService,
        AccountValidator $accountValidator,
        ParametricSyncValidator $parametricSyncValidator,
        ParametricContract $parametricService,
        ?string $accountId = null
    ) {
        $this->http = $http;
        $this->repository = $repository;
        $this->tokenService = $tokenService;
        $this->accountValidator = $accountValidator;
        $this->accountId = $accountId;
        $this->parametricValidator = $parametricSyncValidator;
        $this->parametricService = $parametricService;

        if (!is_null($this->accountId)) {
            $this->bootAuthenticatedClient();
        }
    }

    private function bootAuthenticatedClient(): void
    {
        $this->account = $this->accountValidator->validate($this->accountId);
    }

    public function register(RegisterDTO $dto): string
    {
        if (!is_null($this->accountId)) {
            throw new EmizorApiRegisterException("No se puede registrar usando una instancia vinculada a una cuenta.");
        }

        try {
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

    public function listParametricsTypes(): array
    {
        return $this->parametricService->listParametricTypes();
    }

    public function syncParametrics(array $parametrics): void
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId para usar syncParametrics.");
        }
        $this->parametricValidator->validate($parametrics);

        foreach ($parametrics as $type) {
            $this->parametricService->setHost($this->account->bei_host)->setToken($this->account->bei_token)->sync($type, $this->accountId);
        }
    }

    public function getParametric($type): array
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId para usar getParametrics.");
        }
        $this->parametricValidator->validate([$type]);

        return $this->parametricService->setHost($this->account->bei_host)->setToken($this->account->bei_token)->get($type, $this->accountId);
    }

    public function setDefaults(Closure $callback): self
    {
        $builder = new DefaultsBuilder();

        $callback($builder);

        $defaultsDTO = $builder->build();

        $this->repository->saveDefaults($this->accountId, $defaultsDTO->toArray());

        return $this;
    }

    public function getDefaults(): array
    {
        return $this->repository->getDefaults($this->accountId);
    }

}
