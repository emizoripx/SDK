<?php

namespace Emizor\SDK\Entities;

class BeiInvoiceEntity
{
    public function __construct(
            readonly private string $bei_ticket,
            readonly private string $bei_account_id,
            readonly private ?string $bei_revocation_code,
            readonly private string $bei_step_emission,
            readonly private string $bei_step_revocation,
            readonly private string $bei_amount_total,
            readonly private ?string $bei_sector_document_id,
            readonly private ?string $bei_pos_code,
            readonly private ?string $bei_branch_code,
            readonly private ?string $bei_payment_method,
            readonly private ?array $bei_client,
            readonly private ?array $bei_details,
            readonly private ?array $bei_additional,
            readonly private ?string $bei_emission_date,
            readonly private ?string $bei_revocation_date,
            readonly private ?string $bei_cuf,
            readonly private string $bei_online,
            readonly private ?string $bei_pdf_url,
            readonly private string $bei_giftcard_amount,
            readonly private string $bei_exception_code
            )
    {}

    public function getBeiAccountId(): string
    {
        return $this->bei_account_id;
    }

    public function getBeiRevocationCode(): string
    {
        return $this->bei_revocation_code;
    }

    public function getBeiStepEmission(): string
    {
        return $this->bei_step_emission;
    }

    public function getBeiStepRevocation(): string
    {
        return $this->bei_step_revocation;
    }

    public function getBeiAmountTotal(): string
    {
        return $this->bei_amount_total;
    }

    public function getBeiSectorDocumentId(): string
    {
        return $this->bei_sector_document_id;
    }

    public function getBeiPosCode(): string
    {
        return $this->bei_pos_code;
    }

    public function getBeiBranchCode(): string
    {
        return $this->bei_branch_code;
    }

    public function getBeiPaymentMethod(): string
    {
        return $this->bei_payment_method;
    }

    public function getBeiClient(): string
    {
        return $this->bei_client;
    }

    public function getBeiDetails(): string
    {
        return $this->bei_details;
    }

    public function getBeiAdditional(): string
    {
        return $this->bei_additional;
    }

    public function getBeiEmissionDate(): string
    {
        return $this->bei_emission_date;
    }

    public function getBeiRevocationDate(): string
    {
        return $this->bei_revocation_date;
    }

    public function getBeiCuf(): string
    {
        return $this->bei_cuf;
    }

    public function getBeiOnline(): string
    {
        return $this->bei_online;
    }

    public function getBeiPdfUrl(): string
    {
        return $this->bei_pdf_url;
    }

    public function getBeiGiftcardAmount(): string
    {
        return $this->bei_giftcard_amount;
    }

    public function getBeiExceptionCode(): string
    {
        return $this->bei_exception_code;
    }

    public function getBeiTicket(): string
    {
        return $this->bei_ticket;
    }

    public function jsonSerialize(): array
    {
        return [
           "ticket" => $this->bei_ticket,
           "accountId" => $this->bei_account_id,
           "revocationCode" => $this->bei_revocation_code,
           "amount" => $this->bei_amount_total,
           "typeDocument" => $this->bei_sector_document_id,
           "posCode" => $this->bei_pos_code,
           "branchCode" => $this->bei_branch_code,
           "paymentMethod" => $this->bei_payment_method,
           "client" => $this->bei_client,
           "details" => $this->bei_details,
           "additional" => $this->bei_additional,
           "emissionDate" => $this->bei_emission_date,
           "revocationDate" => $this->bei_revocation_date,
           "cuf" => $this->bei_cuf,
           "online" => $this->bei_online,
           "pdfUrl" => $this->bei_pdf_url,
           "exceptionCode" => $this->bei_exception_code
        ];
    }
}
