<?php

namespace Emizor\SDK\Services\Invoice;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\Invoice\InvoiceEmissionContract;
use Emizor\SDK\Jobs\Details;
use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Models\BeiOfflineInvoiceTracking;
use Emizor\SDK\Models\BeiRequestLogs;
use Emizor\SDK\Transformers\EmissionResource;

class InvoiceEmissionService implements InvoiceEmissionContract
{
    private EmizorApiHttpContract $emizorApiService;

    public function __construct(EmizorApiHttpContract $emizorApiService)
    {
        $this->emizorApiService = $emizorApiService;
    }

    public function emit($ticket):void
    {
        $invoice = BeiInvoice::findByTicket($ticket);


        if ( empty($invoice) || $invoice->isComplete() || $invoice->isInProgress())
            return;

        $data = (new EmissionResource($invoice))->resolve();

        info("EMIT TICKET: ". $ticket . " Data: " , [$data]);

        $id_log_request = BeiRequestLogs::saveLog($ticket, $data, BeiRequestLogs::EMISSION_EVENT);
        $response = null;
        try {
            $response = $this->sendRequestInvoice($invoice, $data);
            info("response " , [$response]);
            $this->handleSuccessfulResponse($invoice, $response, $data, $id_log_request);
        }catch (\Throwable $th) {
            info("Emit errors : TICKET: " . $ticket . " ERROR: ". $th->getMessage() , [$response]);
        }

    }

    private function sendRequestInvoice(BeiInvoice $invoice, array $data):array
    {
        return $this->emizorApiService
            ->setHost($invoice->bei_account->bei_host)
            ->setToken($invoice->bei_account->bei_token)
            ->sendInvoice($data);
    }

    private function handleSuccessfulResponse(BeiInvoice $invoice, array $response, array $data, string $id_log_request)
    {
        if (!empty($response) && isset($response["status"]) && $response["status"] == "success") {
            $ticket = $invoice->getTicket();
            BeiRequestLogs::saveLog($ticket, $data, BeiRequestLogs::EMISSION_EVENT, 200, $id_log_request);

            $invoice->markInProgressEmission();

            info("ANTES =========");
            $invoice->updateBEIfields($response["data"]);
            info("DESPUES =========");
            if ($response["data"]['emission_type_code'] == 2) {
                //OFFLINE LOGIC
                BeiOfflineInvoiceTracking::register($ticket);
            } else {
                //ONLINE LOGIC
                info("Enviando a JOB-DETAILS " . $ticket);
                Details::dispatch($ticket);
            }

            info("Liberando el ticket  " . $ticket);

            return 0;
        } else {
            $this->handleFailureResponse($invoice, $response, $id_log_request);
        }
    }

    private function handleFailureResponse(BeiInvoice $invoice, array $response, string $id_log_request)
    {
        $ticket = $invoice->getTicket();
        info("Emit errors : TICKET: " . $ticket . " ERROR: " ,  $response["errors"]);
        BeiRequestLogs::saveLog($ticket, $response, BeiRequestLogs::EMISSION_EVENT, 400, $id_log_request);

//        event(new FailedInvoiceSin( "Emisión Rechazada Factura #" . $invoice->number, $response["errors"]));
    }
}
