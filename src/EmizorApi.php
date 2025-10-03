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
use OpenApi\Annotations as OA;

/**
 * Main API class for EMIZOR SDK integration.
 *
 * This class provides a unified interface for interacting with EMIZOR services,
 * including account registration, parametric synchronization, invoice management,
 * product homologation, and NIT validation.
 *
 * @package Emizor\SDK
 *
 * @OA\Info(title="EMIZOR SDK API", version="1.0.0", description="SDK for Bolivian electronic invoicing")
 * @OA\Server(url="https://api.emizor.com")
 * @OA\Tag(name="Account", description="Account management operations")
 * @OA\Tag(name="Parametrics", description="Parametric data operations")
 * @OA\Tag(name="Invoices", description="Invoice management operations")
 * @OA\Tag(name="Products", description="Product homologation operations")
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
     * Ensure the instance is authenticated with an account ID.
     *
     * @throws \LogicException If no account ID is set
     */
    private function ensureAuthenticated(): void
    {
        if (is_null($this->accountId)) {
            throw new \LogicException("Debes instanciar con accountId para usar esta funcionalidad.");
        }
    }

    /**
     * Ensure the instance is not authenticated (for registration).
     *
     * @throws EmizorApiRegisterException If account ID is already set
     */
    private function ensureNotAuthenticated(): void
    {
        if (!is_null($this->accountId)) {
            throw new EmizorApiRegisterException("No se puede registrar usando una instancia vinculada a una cuenta.");
        }
    }

    /**
     * Register a new account with EMIZOR.
     *
     * @param RegisterDTO $dto Data transfer object containing registration details
     * @return string The account ID
     * @throws EmizorApiRegisterException If registration fails
     *
     * @OA\Post(
     *     path="/register",
     *     tags={"Account"},
     *     summary="Register a new EMIZOR account",
     *     description="Creates a new account with the provided credentials",
     *     @OA\RequestBody(
     *         required=true,
     *         description="Registration data",
     *         @OA\JsonContent(
     *             required={"clientId","clientSecret","host"},
     *             @OA\Property(property="clientId", type="string", example="client_123"),
     *             @OA\Property(property="clientSecret", type="string", example="secret_456"),
     *             @OA\Property(property="host", type="string", example="PILOTO"),
     *             @OA\Property(property="demo", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Account registered successfully",
     *         @OA\JsonContent(type="string", example="uuid-account-id")
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Registration failed"
     *     )
     * )
     */
    public function register(RegisterDTO $dto): string
    {
        $this->ensureNotAuthenticated();

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
     *
     * @OA\Post(
     *     path="/sync-parametrics",
     *     tags={"Parametrics"},
     *     summary="Synchronize parametric data",
     *     description="Syncs fiscal parametric data from EMIZOR API",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(type="string", example="actividades"),
     *             description="List of parametric types"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Parametrics synced successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - account not authenticated"
     *     )
     * )
     */
    public function syncParametrics(array $parametrics): void
    {
        $this->ensureAuthenticated();
        $this->parametricValidator->validate($parametrics);

        foreach ($parametrics as $type) {
            $this->parametricService->setHost($this->account->bei_host)->setToken($this->account->bei_token)->sync($type, $this->accountId);
        }
    }

    public function getParametric($type): array
    {
        $this->ensureAuthenticated();
        $this->parametricValidator->validate([$type]);

        return $this->parametricService->get($type, $this->accountId);
    }

    public function setDefaults(Closure $callback): self
    {
        $this->ensureAuthenticated();
        $builder = new DefaultsBuilder();

        $callback($builder);

        $defaultsDTO = $builder->build();

        $this->repository->saveDefaults($this->accountId, $defaultsDTO->toArray());

        return $this;
    }

    public function getDefaults(): array
    {
        $this->ensureAuthenticated();
        return $this->repository->getDefaults($this->accountId);
    }

    public function homologateProduct(array $products): void
    {
        $this->ensureAuthenticated();
        $this->productService->homologate($products, $this->accountId);
    }

    public function homologateProductList():array
    {
        $this->ensureAuthenticated();
        return $this->productService->listHomologate($this->accountId);
    }

    /**
     * Issue an electronic invoice.
     *
     * @param Closure $callback Builder callback to configure the invoice
     * @param string $ticket Unique ticket for the invoice
     * @return self
     * @throws \LogicException If no account ID is set
     *
     * @OA\Post(
     *     path="/issue-invoice",
     *     tags={"Invoices"},
     *     summary="Issue an electronic invoice",
     *     description="Creates and emits an electronic invoice",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"ticket"},
     *             @OA\Property(property="ticket", type="string", example="INV-123"),
     *             @OA\Property(property="invoice_data", type="object", description="Invoice configuration via callback")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice issued successfully"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - account not authenticated"
     *     )
     * )
     */
    public function issueInvoice(Closure $callback, string $ticket): self
    {
        $this->ensureAuthenticated();

        $this->invoiceManager->createAndEmitInvoice($callback, $ticket, $this->accountId);

        return $this;
    }

    public function validateNit($nit): array
    {
        $this->ensureAuthenticated();

        return $this->nitValidationService->validate($this->account->bei_host, $this->account->bei_token, $nit);
    }

    public function revocateInvoice(string $ticket, int $revocationReasonCode):void
    {
        $this->ensureAuthenticated();
        $this->invoiceManager->revocateInvoice($ticket, $revocationReasonCode);
    }
}
