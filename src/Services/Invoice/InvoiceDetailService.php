<?php

namespace Emizor\SDK\Services\Invoice;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Events\InvoiceAccepted;
use Emizor\SDK\Events\InvoiceRejected;
use Emizor\SDK\Events\InvoiceRevocated;
use Emizor\SDK\Events\InvoiceRevocationRejected;
use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Models\BeiOfflineInvoiceTracking;
use Emizor\SDK\Models\BeiRequestLogs;
use Exception;

class InvoiceDetailService
{
    private EmizorApiHttpContract $emizorApiService;

    public function __construct(EmizorApiHttpContract $emizorApiService)
    {
        $this->emizorApiService = $emizorApiService;
    }

    public function getDetails($ticket)
    {
        $invoice = BeiInvoice::findByTicket($ticket);
        info("get details " , [$invoice]);
        if ( $invoice->notEmitted() || ($invoice->isComplete() && $invoice->notRevocated()) || $invoice->isCompleteRevocation()  ) {
            info("Get-Details INVOICE WITH INCORRECT STATUS " . " TICKET: " . $ticket);
            return 0;
        }

        $id_log_response = BeiRequestLogs::saveLog($ticket, [], BeiRequestLogs::GET_DETAIL_EVENT);

        $response = null;
        try {
            $response = $this->sendRequestDetailInvoice($invoice);
            info("response " , [$response]);
            $this->handleSuccessfulResponse($invoice, $response, $id_log_response);
        }catch (\Throwable $th) {
            info("Emit errors : TICKET: " . $ticket . " ERROR: ". $th->getMessage() , [$response]);
        }
    }


    private function sendRequestDetailInvoice(BeiInvoice $invoice):array
    {
        return $this->emizorApiService->getDetailInvoice($invoice->bei_account->bei_host, $invoice->bei_account->bei_token, $invoice->bei_ticket);
    }

    private function handleSuccessfulResponse(BeiInvoice $invoice, array $response, string $id_log_response)
    {
        $ticket = $invoice->bei_ticket;

        if (!empty($response) && isset($response["status"]) && $response["status"] == "success") {

            $response = $response["data"];
            BeiRequestLogs::saveLog($ticket, $response, BeiRequestLogs::GET_DETAIL_EVENT, 200, $id_log_response);
            try {
                $this->processResponse($invoice, $response);

                $invoice->updateBEIfields($response);

                BeiOfflineInvoiceTracking::remove($ticket);
                return;

            } catch (\Throwable $th) {
                info("error " . $th->getMessage());
            }

        } else {
            BeiRequestLogs::saveLog($ticket, $response["errors"]??[], BeiRequestLogs::GET_DETAIL_EVENT,400, $id_log_response);
            throw new Exception("Problema al obtener detalle de Factura " . $ticket . " volviendo a la cola.");

        }

    }

    public function processResponse(BeiInvoice $invoice, $response)
    {
        $ticket = $invoice->bei_ticket;

        if (in_array($response['estado'], ["INVOICE_STATE_IN_QUEUE",'INVOICE_STATE_QUEUE_PENDING', "INVOICE_REVOCATION_STATE_IN_QUEUE"])) {
            throw new Exception("Factura " . $ticket . " en estado " . $response['estado'] . " aun en espera, volviendo a la cola.");
        }


        if ($invoice->notRevocated() ) {

            if ($invoice->isInProgress()) {
                $invoice->markCompleteEmission();
                event(new InvoiceAccepted(ticket: $ticket, status: 690, meta:[]));
            }

            if ($response['estado'] == "INVOICE_STATE_SENT_TO_SIN_INVALID" ) {
                BeiOfflineInvoiceTracking::remove($ticket);
                event(new InvoiceRejected(ticket: $ticket,status: 902,meta:["description" => "" , "errors" => $this->getErrorRejection($response)]));
            }

        }else {

            //revocation
            if( isset($response['errores']) && is_array($response['errores']) && !empty($response['errores']) ) {
                // case in which invoice was used by final client.
                $error_revocation_invoice = array_filter(
                    json_decode(json_encode($response['errores'], true), false),
                    function ($e) {
                        return $e->code == 997;
                    }
                );

                if (!empty($error_revocation_invoice)) {
                    event(new InvoiceRevocationRejected(ticket:$ticket, status:690, meta:[ "description" =>"Anulación Rechazada factura consolidada ó usada."]));
                }
            }

            if ($invoice->isInProgressRevocation()) {
                $invoice->markCompleteRevocation();
                event(new InvoiceRevocated(ticket: $ticket,status: 691,meta:[]));
            }

        }

    }

    private function getErrorRejection($response)
    {
        $error = 'Error no controlado';
        if (isset($response['errores']) && is_array($response['errores']) && !empty($response['errores'])) {
            // case in which invoice was used by final client.
            $error = array_filter(
                json_decode(json_encode($response['errores'], true), false),
                function ($e) {
                    return $e->code == 0 && !$e->warning;
                }
            );

            if (!empty($error)) {
                return "La factura contiene caracteres no válidos. Revise los datos";
            }
        }

        return $error;

    }

}
