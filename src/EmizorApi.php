<?php

namespace Emizor\SDK;

use Closure;
use Emizor\SDK\Builders\DefaultsBuilder;
use Emizor\SDK\Contracts\EmizorApiContract;
use Emizor\SDK\Contracts\HomologateProductContract;
use Emizor\SDK\Contracts\Invoice\InvoiceManagerContract;
use Emizor\SDK\Contracts\NitValidationContract;
use Emizor\SDK\Contracts\ParametricContract;
use Emizor\SDK\DTO\RegisterDTO;
use Emizor\SDK\Exceptions\EmizorApiRegisterException;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Validators\AccountValidator;
use Emizor\SDK\Validators\ParametricSyncValidator;

/**
 * Main API class for EMIZOR SDK integration.
 *
 * This class provides a unified interface for interacting with EMIZOR services,
 * including account registration, parametric synchronization, invoice management,
 * product homologation, and NIT validation.
 *
 * @package Emizor\SDK
 */
class EmizorApi implements EmizorApiContract
{
    private ?string $accountId;
    private ?BeiAccount $account;
    private ParametricContract $parametricService;
    private AccountRepository $repository;
    private AccountValidator $accountValidator;
    private ParametricSyncValidator $parametricValidator;
    private HomologateProductContract $productService;
    private InvoiceManagerContract $invoiceManager;
    private NitValidationContract $nitValidationService;

    public function __construct(
        AccountRepository $repository,
        AccountValidator $accountValidator,
        ParametricSyncValidator $parametricSyncValidator,
        ParametricContract $parametricService,
        HomologateProductContract $productService,
        InvoiceManagerContract $invoiceManager,
        NitValidationContract $nitValidationContract,
        ?string $accountId = null
    ) {
        $this->repository = $repository;
        $this->accountValidator = $accountValidator;
        $this->accountId = $accountId;
        $this->parametricValidator = $parametricSyncValidator;
        $this->parametricService = $parametricService;
        $this->productService = $productService;
        $this->invoiceManager = $invoiceManager;
        $this->nitValidationService = $nitValidationContract;

        if (!is_null($this->accountId)) {
            $this->bootAuthenticatedClient();
        }
    }

    private function bootAuthenticatedClient(): void
    {
        $this->account = $this->accountValidator->validate($this->accountId);
    }

    /**
     * Register a new account with EMIZOR.
     *
     * @param RegisterDTO $dto Data transfer object containing registration details
     * @return string The account ID
     * @throws EmizorApiRegisterException If registration fails
     */
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

    /**
     * Synchronize parametric data from EMIZOR API.
     *
     * @param array $parametrics List of parametric types to sync
     * @return void
     * @throws \LogicException If no account ID is set
     */
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

        return $this->parametricService->get($type, $this->accountId);
    }

    public function setDefaults(Closure $callback): self
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId para definir las configuraciones por defecto.");
        }
        $builder = new DefaultsBuilder();

        $callback($builder);

        $defaultsDTO = $builder->build();

        $this->repository->saveDefaults($this->accountId, $defaultsDTO->toArray());

        return $this;
    }

    public function getDefaults(): array
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId para obtener las configuraciones por defecto.");
        }
        return $this->repository->getDefaults($this->accountId);
    }

    public function homologateProduct(array $products): void
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId para usar la homologación de productos.");
        }
        $this->productService->homologate($products, $this->accountId);
    }

    public function homologateProductList():array
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId para usar el listado de homologación.");
        }
        return $this->productService->listHomologate($this->accountId);
    }

    /**
     * Issue an electronic invoice.
     *
     * @param Closure $callback Builder callback to configure the invoice
     * @param string $ticket Unique ticket for the invoice
     * @return self
     * @throws \LogicException If no account ID is set
     */
    public function issueInvoice(Closure $callback, string $ticket): self
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId.");
        }

        $this->invoiceManager->createAndEmitInvoice($callback, $ticket, $this->accountId);

        return $this;
    }

    public function validateNit($nit): array
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId.");
        }

        return $this->nitValidationService->validate($this->account->bei_host, $this->account->bei_token, $nit);
    }

    public function revocateInvoice(string $ticket, int $revocationReasonCode):void
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId.");
        }
        $this->invoiceManager->revocateInvoice($ticket, $revocationReasonCode);
    }
}
