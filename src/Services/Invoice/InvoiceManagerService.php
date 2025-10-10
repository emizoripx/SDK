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
use Emizor\SDK\Repositories\AccountRepository;
use Emizor\SDK\Repositories\InvoiceRepository;

class InvoiceManagerService implements InvoiceManagerContract
{
    protected HttpClientInterface $http;
    protected InvoiceRepository $repository;
    private AccountRepository $repositoryAccount;

    public function __construct( InvoiceRepository $repository, AccountRepository $repositoryAccount)
    {
        $this->repository = $repository;
        $this->repositoryAccount = $repositoryAccount;
    }


    public function createAndEmitInvoice(Closure $callback, string $ticket, string $accountId): void
    {
        $defaults = $this->repositoryAccount->getDefaults($accountId);

        $builder = new InvoiceBuilder($ticket, $defaults);
        $callback($builder, $ticket);

        $payload = $builder->build();

        $this->repository->store([...$payload->toArray(),"bei_account_id" => $accountId]);
        info("===================>validating nit");
        ValidateNit::dispatchSync($ticket);
        info("===================>emiting invoice");
        Emit::dispatchSync($ticket);
    }

    public function revocateInvoice(string $ticket, int $revocationReasonCode):void
    {
        Revocate::dispatchSync($ticket, $revocationReasonCode);
    }

}
