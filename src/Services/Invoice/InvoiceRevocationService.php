<?php

namespace Emizor\SDK\Services\Invoice;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\Invoice\InvoiceRevocationContract;
use Emizor\SDK\Jobs\Details;
use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Models\BeiRequestLogs;
use Exception;

class InvoiceRevocationService implements InvoiceRevocationContract
{
    private EmizorApiHttpContract $emizorApiService;

    public function __construct(EmizorApiHttpContract $emizorApiService)
    {
        $this->emizorApiService = $emizorApiService;
    }

    public function revocate(string $ticket, int $revocationReasonCode): void
    {
        $invoice = BeiInvoice::findByTicket($ticket);
        info("revocate " , [$invoice]);

        if ( $invoice->isInProgress() || $invoice->isCompleteRevocation() || $invoice->isInProgressRevocation()){
            info("REVOCATE WITH INCORRECT STATUS " . " TICKET: " . $ticket);
            return;
        }
        $id_log_request = BeiRequestLogs::saveLog($ticket, ["revocation_code"=> $invoice->bei_revocation_code], BeiRequestLogs::REVOCATION_EVENT);

        $response = null;
        try {
            $response = $this->sendRequestRevocateInvoice($invoice, $revocationReasonCode);
            $invoice->markSentRevocation();
            info("response " , [$response]);
            $this->handleSuccessfulResponse($invoice, $response, $id_log_request);
        }catch (\Throwable $th) {
            info("Emit errors : TICKET: " . $ticket . " ERROR: ". $th->getMessage() , [$response]);
        }
    }


    private function sendRequestRevocateInvoice(BeiInvoice $invoice, int $revocationReasonCode):array
    {
        return $this->emizorApiService
            ->setHost($invoice->bei_account->bei_host)
            ->setToken($invoice->bei_account->bei_token)
            ->revocateInvoice($invoice->bei_cuf,$revocationReasonCode );
    }

    private function handleSuccessfulResponse(BeiInvoice $invoice, array $response, string $id_log_request)
    {
        $ticket = $invoice->getTicket();

        if (!empty($response) && isset($response["status"]) && $response["status"] == "success") {

            $response = $response["data"];
            BeiRequestLogs::saveLog($ticket, $response, BeiRequestLogs::REVOCATION_EVENT,200, $id_log_request);

            $invoice->markInProgressRevocation();

            Details::dispatch($ticket);
            return;

        } else {
            BeiRequestLogs::saveLog($ticket, $response["errors"]??[], BeiRequestLogs::GET_DETAIL_EVENT,400, $id_log_request);
//            event(new FailedInvoiceSin($invoice->account_id, "Anulación Rechazada de la Factura #" . $invoice->number , $response["errors"]??[]));
            throw new Exception("Problema al obtener detalle de Factura " . $ticket . " volviendo a la cola.");

        }

    }


}
