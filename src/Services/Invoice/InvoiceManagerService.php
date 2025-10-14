<?php

namespace Emizor\SDK\Services\Invoice;

use Closure;
use Emizor\SDK\Builders\InvoiceBuilder;
use Emizor\SDK\Contracts\HttpClientInterface;
use Emizor\SDK\Contracts\Invoice\InvoiceManagerContract;
use Emizor\SDK\DTO\InvoiceDTO;
use Emizor\SDK\Entities\BeiInvoiceEntity;
use Emizor\SDK\Jobs\Emit;
use Emizor\SDK\Jobs\Revocate;
use Emizor\SDK\Jobs\ValidateNit;
use Emizor\SDK\Mappers\BeiInvoiceMapper;
use Emizor\SDK\Models\BeiAccount;
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Repositories\InvoiceRepository;

class InvoiceManagerService implements InvoiceManagerContract
{
    protected HttpClientInterface $http;
    protected InvoiceRepository $invoiceRepository;
    private AccountRepository $repositoryAccount;

    public function __construct( InvoiceRepository $invoiceRepository, AccountRepository $repositoryAccount)
    {
        $this->invoiceRepository = $invoiceRepository;
        $this->repositoryAccount = $repositoryAccount;
    }


    public function createAndEmitInvoice(Closure $callback, string $ticket, BeiAccount $beiAccount): void
    {
        $beiInvoice = $this->invoiceRepository->findByTicket($ticket);
        if (empty($beiInvoice)) { // TODO: refactor logic for retry same ticket

            $defaults = $this->repositoryAccount->getDefaults($beiAccount->id);

            $builder = new InvoiceBuilder($ticket, $defaults);
            $callback($builder, $ticket);

            $payload = $builder->build();

            $this->invoiceRepository->store([...$payload->toArray(),"bei_account_id" => $beiAccount->id]);
        }
        info("===================>validating nit");
        ValidateNit::dispatchSync($ticket);
        info("===================>emiting invoice");
        Emit::dispatch($ticket);
    }

    public function revokeInvoice(string $ticket, int $revocationReasonCode):void
    {
        $beiInvoice = $this->invoiceRepository->findByTicket($ticket);
        if (empty($beiInvoice)) {
            throw new \Exception("Invoice with ticket: $ticket not found");
        }
        Revocate::dispatchSync($ticket, $revocationReasonCode);
    }

    public function getInvoice(string $ticket): BeiInvoiceEntity
    {
        $beiInvoice = $this->invoiceRepository->findByTicket($ticket);
        if (empty($beiInvoice)) {
            throw new \Exception("Invoice with ticket: $ticket not found");
        }
        return BeiInvoiceMapper::toArray($beiInvoice);
    }
}
