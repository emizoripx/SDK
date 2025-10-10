<?php

declare(strict_types=1);

namespace Emizor\SDK\Mappers;

use Emizor\SDK\Entities\BeiInvoiceEntity;
use Emizor\SDK\Models\BeiInvoice;

class BeiInvoiceMapper
{
    public static function toArray(BeiInvoice $beiInvoice): BeiInvoiceEntity
    {
        return new BeiInvoiceEntity(
            $beiInvoice->bei_ticket,
            $beiInvoice->bei_account_id,
            (string) $beiInvoice->bei_revocation_code,
            $beiInvoice->bei_step_emission,
            $beiInvoice->bei_step_revocation,
            (string) $beiInvoice->bei_amount_total,
            (string) $beiInvoice->bei_sector_document_id,
            (string) $beiInvoice->bei_pos_code,
            (string) $beiInvoice->bei_branch_code,
            (string) $beiInvoice->bei_payment_method,
            $beiInvoice->bei_client,
            $beiInvoice->bei_details,
            $beiInvoice->bei_additional,
            $beiInvoice->bei_emission_date ? $beiInvoice->bei_emission_date->toISOString() : null,
            $beiInvoice->bei_revocation_date ? $beiInvoice->bei_revocation_date->toISOString() : null,
            $beiInvoice->bei_cuf,
            (string) $beiInvoice->bei_online,
            $beiInvoice->bei_pdf_url,
            (string) $beiInvoice->bei_giftcard_amount,
            $beiInvoice->bei_exception_code
        );
    }

}
