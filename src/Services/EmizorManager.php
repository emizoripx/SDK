<?php

namespace Emizor\SDK\Services;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\HomologateProductContract;
use Emizor\SDK\Mappers\BeiInvoiceMapper;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Enums\InvoiceType;
use Emizor\SDK\Repositories\InvoiceRepository;
use Emizor\SDK\Services\ParametricService;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Builders\DefaultsBuilder;

class EmizorManager
{
    protected EmizorApiHttpContract $apiService;

    public function __construct(
        protected BeiAccount $credential,
        EmizorApiHttpContract $apiService,
        protected ParametricService $parametricService,
        protected AccountRepository $accountRepository,
        protected HomologateProductContract $homologateService,
        protected InvoiceRepository $invoiceRepository
    ) {
        $this->apiService = $apiService;
    }

    public function emitInvoice(array $data)
    {
        return $this->apiService->sendInvoice($this->credential->bei_host, $this->credential->bei_token, $data);
    }

    public function revokeInvoice(string $ticket, int $revocationReasonCode)
    {
        return $this->apiService->revocateInvoice($this->credential->bei_host, $this->credential->bei_token, $ticket, $revocationReasonCode);
    }

    public function getInvoiceDetails(string $ticket)
    {
        return $this->apiService->getDetailInvoice($this->credential->bei_host, $this->credential->bei_token, $ticket);
    }

    public function validateNit(string $nit)
    {
        return $this->apiService->checkNit($this->credential->bei_host, $this->credential->bei_token, $nit);
    }

    public function getParametric(string $type)
    {
        return $this->parametricService->get($type, $this->credential->id);
    }

    public function getParametrics(string $type)
    {
        return $this->apiService->getParametrics($this->credential->bei_host, $this->credential->bei_token, $type);
    }

    public function syncParametrics(array $types)
    {
        foreach ($types as $type) {
            $this->parametricService->sync($this->credential->bei_host, $this->credential->bei_token, $type, $this->credential->id);
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
        $beiInvoiceEntity = BeiInvoiceMapper::toArray($this->invoiceRepository->get($ticket));
        return $beiInvoiceEntity->jsonSerialize();
    }
}
