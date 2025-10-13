<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\HomologateProductContract;
use Emizor\SDK\Contracts\Invoice\InvoiceManagerContract;
use Emizor\SDK\Contracts\TokenContract;
use Emizor\SDK\Mappers\BeiInvoiceMapper;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Enums\InvoiceType;
use Emizor\SDK\Repositories\InvoiceRepository;
use Emizor\SDK\Services\Invoice\InvoiceManagerService;
use Emizor\SDK\Services\ParametricService;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Builders\DefaultsBuilder;

class EmizorManager
{
    public function __construct(
        protected BeiAccount $credential,
        protected ParametricService $parametricService,
        protected AccountRepository $accountRepository,
        protected HomologateProductContract $homologateService,
        protected InvoiceRepository $invoiceRepository,
        protected InvoiceManagerContract $invoiceManagerService,
        protected TokenManager $tokenManager
    ) {}

    public function emitInvoice(Callable $callback, string $ticket): void
    {
        $this->invoiceManagerService->createAndEmitInvoice($callback, $ticket, $this->credential);
    }

    public function revokeInvoice(string $ticket, int $revocationReasonCode):void
    {
        $this->invoiceManagerService->revokeInvoice($ticket, $revocationReasonCode);
    }

    public function getParametric(string $type)
    {
        return $this->parametricService->getParametric($this->credential, $type);
    }

    public function syncParametrics(array $types):void
    {
        foreach ($types as $type) {
            $this->parametricService->syncAndSaveParametric($this->credential, $type);
        }
    }

    public function setDefaults(callable $callback)
    {
        $builder = new DefaultsBuilder();
        $callback($builder);
        $defaultsDTO = $builder->build();
        $this->accountRepository->saveDefaults($this->credential->id, $defaultsDTO->toArray());
    }

    public function getDefaults(): array
    {
        return $this->accountRepository->getDefaults($this->credential->id);
    }

    public function homologateProduct(array $products)
    {
        $this->homologateService->homologate($products, $this->credential->id);
    }

    public function homologateProductList(): array
    {
        return $this->homologateService->listHomologate($this->credential->id);
    }

    public function getInvoice(string $ticket): array
    {
        $beiInvoiceEntity = $this->invoiceManagerService->getInvoice($ticket);
        return $beiInvoiceEntity->jsonSerialize();
    }

    public function refreshToken(): void
    {
        $this->tokenManager->generateAndSaveToken($this->credential);
    }
}
