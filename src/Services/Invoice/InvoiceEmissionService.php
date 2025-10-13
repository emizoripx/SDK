<?php

namespace Emizor\SDK\Services\Invoice;

use Emizor\SDK\Contracts\EmizorApiHttpContract;
use Emizor\SDK\Contracts\Invoice\InvoiceEmissionContract;
use Emizor\SDK\Events\FailedInvoiceEmission;
use Emizor\SDK\Events\InvoiceRejected;
use Emizor\SDK\Exceptions\EmizorApiAssociationException;
use Emizor\SDK\Jobs\Details;
use Emizor\SDK\Models\BeiInvoice;
use Emizor\SDK\Models\BeiOfflineInvoiceTracking;
use Emizor\SDK\Models\BeiProduct;
use Emizor\SDK\Models\BeiRequestLogs;
use Emizor\SDK\Transformers\EmissionResource;

class InvoiceEmissionService implements InvoiceEmissionContract
{
    private EmizorApiHttpContract $emizorApiService;

    public function __construct(EmizorApiHttpContract $emizorApiService)
    {
        $this->emizorApiService = $emizorApiService;
    }

    public function emit($ticket): void
    {
        $invoice = BeiInvoice::findByTicket($ticket);

        if (empty($invoice) || $invoice->isComplete() || $invoice->isInProgress()) {
            return;
        }

        try {
            $data = (new EmissionResource($invoice))->resolve();

            info("EMIT TICKET: {$ticket} - Preparing emission data");

            $id_log_request = BeiRequestLogs::saveLog($ticket, $data, BeiRequestLogs::EMISSION_EVENT);

            $response = $this->sendRequestInvoice($invoice, $data);

            $this->handleSuccessfulResponse($invoice, $response, $data, $id_log_request);
        } catch (\Throwable $th) {
            info("Emission failed for ticket {$ticket}: {$th->getMessage()}" . " file ". $th->getFile() . " Line: " . $th->getLine());
            $this->handleFailureResponse($invoice, ['errors' => $th->getMessage()], $id_log_request);
        }

    }

    private function sendRequestInvoice(BeiInvoice $invoice, array $data): array
    {
        return $this->emizorApiService->sendInvoice($invoice->bei_account->bei_host, $invoice->bei_account->bei_token, $data);
    }

    private function handleSuccessfulResponse(BeiInvoice $invoice, array $response, array $data, string $id_log_request)
    {
        if (!empty($response) && isset($response["status"]) && $response["status"] == "success") {
            $ticket = $invoice->getTicket();
            BeiRequestLogs::saveLog($ticket, $data, BeiRequestLogs::EMISSION_EVENT, 200, $id_log_request);

            $invoice->markInProgressEmission();
            $invoice->updateBEIfields($response["data"]);

            if ($response["data"]['emission_type_code'] == 2) {
                // OFFLINE EMISSION
                BeiOfflineInvoiceTracking::register($ticket);
                info("Offline emission registered for ticket {$ticket}");
            } else {
                // ONLINE EMISSION
                Details::dispatch($ticket);
                info("Dispatched details job for online emission ticket {$ticket}");
            }

            return;
        } else {
            $this->handleFailureResponse($invoice, $response, $id_log_request);
        }
    }

    private function handleFailureResponse(BeiInvoice $invoice, array $response, string $id_log_request)
    {
        $ticket = $invoice->getTicket();
        $errors = $response["errors"] ?? 'Unknown error';

        // Handle case where errors is an array or nested structure
        if (is_array($errors)) {
            $errors = $this->flattenErrors($errors);
        }

        info("Emission failed for ticket {$ticket}: {$errors}");
        BeiRequestLogs::saveLog($ticket, $response, BeiRequestLogs::EMISSION_EVENT, 400, $id_log_request);

        event(new InvoiceRejected($ticket, -1,$errors));
    }

    /**
     * Flatten nested error arrays into a readable string
     */
    private function flattenErrors($errors): string
    {
        if (is_string($errors)) {
            return $errors;
        }

        if (is_array($errors)) {
            $flattened = [];
            foreach ($errors as $key => $value) {
                if (is_array($value)) {
                    $flattened[] = $key . ': ' . $this->flattenErrors($value);
                } else {
                    $flattened[] = $key . ': ' . $value;
                }
            }
            return implode(' | ', $flattened);
        }

        return (string) $errors;
    }
}
