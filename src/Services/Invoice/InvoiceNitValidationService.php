<?php

namespace Emizor\SDK\Services\Invoice;

use Emizor\SDK\Contracts\NitValidationContract;
use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Models\BeiRequestLogs;
use Throwable;

class InvoiceNitValidationService
{
    private NitValidationContract $nitValidationService;

    public function __construct(NitValidationContract $nitValidationService)
    {
        $this->nitValidationService = $nitValidationService;
    }

    public function validaNit($ticket):void
    {
        $invoice = BeiInvoice::findByTicket($ticket);

        if (empty($invoice) || $invoice->isInProgress() || $invoice->isCompleteRevocation() || $invoice->isInProgressRevocation())
            return;


        $bei_client_document_number_type = $invoice->bei_client["client_document_number_type"];
        $bei_client_document_number = $invoice->bei_client["client_document_number"];

        if ($bei_client_document_number_type == "5") {
            info('START CHECKING DOCUMENT NUMBER');
            $identity_document_number_detected = $this->checkDocumentNumberType($bei_client_document_number);
            if (in_array($identity_document_number_detected,[1,4])) {
                info('STORE DOCUMENT NUMBER TYPE DETECTED '. $identity_document_number_detected);
                    $invoice->bei_client_identity_document_id = $identity_document_number_detected;
                    $invoice->saveQuietly();
                info('START PROCESSING VALIDATION caso 2 guardando');
                return ;
            }

        }

        info('START PROCESSING VALIDATION');

        $id_log_request = BeiRequestLogs::saveLog($ticket , [$bei_client_document_number], BeiRequestLogs::VALIDATION_NIT);
        try {
            $response = $this->nitValidationService->validate($invoice->bei_account->bei_host,$invoice->bei_account->bei_token, $bei_client_document_number);
            info("data",[gettype($response), $response]);
            $this->handleSuccessfulResponse($invoice, $response, $id_log_request,$bei_client_document_number);
        }catch(Throwable) {

        }



    }

    private function handleSuccessfulResponse(BeiInvoice $invoice, array $response, string $id_log_request, string $bei_client_document_number)
    {
        info("RESPONSE " , [$response]);
        if (!empty($response) && isset($response["status"]) && $response["status"] == "success") {

            BeiRequestLogs::saveLog($invoice->bei_ticket . " NIT =" . $bei_client_document_number, $response["data"], BeiRequestLogs::VALIDATION_NIT, 200, $id_log_request);
            $invoice->bei_exception_code = ($response["data"]["codigo"] == 994)?1:0;
            $invoice->saveQuietly();
            return 0;
        } else {
            BeiRequestLogs::saveLog($invoice->bei_ticket . " NIT =" . $bei_client_document_number, ["errors"], BeiRequestLogs::VALIDATION_NIT, 200, $id_log_request);
            $invoice->bei_exception_code = 1;
            $invoice->saveQuietly();
            return 0;
        }
    }

    private function checkDocumentNumberType($numero)
    {
        // Validar si la entrada tiene letras
        if (preg_match('/[a-zA-Z]/', $numero)) {
            info("ingresando validado 1");
            return 4; // Tipo de documento 4 (Otros)
        }

        $longitud = strlen($numero);
        info("ingresando validado 2 LONGITUD ====> " . $longitud);
        // Validar si la longitud es mayor a 4 y menor a 9
        if ($longitud > 4 && $longitud < 9) {
            info("ingresando validado 2");
            return 1; // Tipo de documento 1 (CI)
        }

        // Validar si la longitud es mayor a 8 y menor a 13
        if ($longitud > 8 && $longitud < 13) {
            info("ingresando validado 3");
            return 5; // Tipo de documento 5 (NIT)
        }
        info("ingresando validado 4");
        return 4; // Tipo de documento 4 (Otros) por defecto
    }

}
