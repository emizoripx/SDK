<?php

namespace Emizor\SDK\Services\Invoice;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\Invoice\InvoiceManagerContract;
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
                info("ANTES DETAILSSS ===============");
                $invoice->updateBEIfields($response);
                info("DEPUES DETAILSSS ===============");
                BeiOfflineInvoiceTracking::remove($ticket);
                return;

            } catch (\Throwable $th) {
                if($th->getMessage() == "CODE_997_REVOCATION") {
//                    event(new FailedInvoiceSin($this->company_id, "Anulación Rechazada de la Factura #" . $invoice->number, " Factura consolidada ó usada."));
                    return;
                }

                if($th->getMessage() == "CODE_902_EMISSION") {
                    $invoice->updateBEIfields($response);
                    BeiOfflineInvoiceTracking::remove($ticket);
//                    event(new FailedInvoiceSin($this->company_id, "Emisión Rechazada de la Factura #" . $invoice->number, $this->getErrorRejection($response)));
                    return;
                }
            }

        } else {
            BeiRequestLogs::saveLog($ticket, $response["errors"]??[], BeiRequestLogs::GET_DETAIL_EVENT,400, $id_log_response);
            throw new Exception("Problema al obtener detalle de Factura " . $ticket . " volviendo a la cola.");

        }

    }

    public function processResponse(BeiInvoice $invoice, $response)
    {

        if ($invoice->notRevocated() ) {
            // if not send to revocate, then wait for emission to be complete

            if ($response['estado'] == "INVOICE_STATE_IN_QUEUE" || $response['estado'] == 'INVOICE_STATE_QUEUE_PENDING') {
                throw new Exception("Factura " . $this->ticket . " en estado " . $response['estado'] . " aun en espera, volviendo a la cola.");
            }

            if ($invoice->isInProgress()) {
                $invoice->markCompleteEmission();
            }

            if ($response['estado'] == "INVOICE_STATE_SENT_TO_SIN_INVALID" ) {
                throw new Exception("CODE_902_EMISSION");
            }

        }else {
            // in the other hand, wait for revocation to be complete
            if ($response['estado'] == "INVOICE_REVOCATION_STATE_IN_QUEUE") {
                throw new Exception("Factura " . $this->ticket . " en estado " . $response['estado'] . " aun en espera, volviendo a la cola.");
            }
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
                    throw new Exception("CODE_997_REVOCATION");

                }
            }

            if ($invoice->isInProgressRevocation()) {
                $invoice->markCompleteRevocation();
               // event revocation
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
                return "La factura contine caracteres no válidos. Contáctese con soporte técnico.";
            }
        }

        return $error;

    }

}
