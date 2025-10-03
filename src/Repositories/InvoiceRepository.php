<?php

namespace Emizor\SDK\Repositories;

use Carbon\Carbon;
use Emizor\SDK\Enums\InvoiceType;
use Emizor\SDK\Models\BeiInvoice;

class InvoiceRepository
{
    public function store(array $data): string
    {
        $new =BeiInvoice::factory()->create(
            [
                'bei_ticket' => $data['bei_ticket'],
                'bei_account_id' => $data['bei_account_id'],
                'bei_amount_total' => $data['bei_amount_total'],
                'bei_sector_document_id' => InvoiceType::from((int)$data['bei_sector_document_id']) ,
                'bei_pos_code' => $data['bei_pos_code'],
                'bei_branch_code' => $data['bei_branch_code'],
                'bei_payment_method' => $data['bei_payment_method'],
                'bei_client' => $data['bei_client'],
                'bei_details' => $data['bei_details'],
            ]
        );

        return $new['bei_ticket'];
    }

    public function get($ticket)
    {
        return BeiInvoice::where("beiTicket")->first();
    }

    public function update($ticket, $response)
    {
        BeiInvoice::where("bei_ticket", $ticket)->update(
            [
                'bei_additional' =>
                    [
                        'leyenda' => isset($data['leyenda']) ?  $data['leyenda']: "",
                        'urlSin' => isset($data['urlSin']) ? $data['urlSin']:"",
                        'pdf_url' => isset($data['pdf_url']) ?$data['pdf_url']:"",
                        'sucursal' => isset($data['sucursal']) ? $data['sucursal'] :"",
                    ],
                'bei_cuf' => $response["cuf"],
                'bei_emission_date' => Carbon::parse($response["fechaEmision"])->toDateTimeString()
            ]
        );
    }


}
