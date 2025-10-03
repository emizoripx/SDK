<?php

namespace Emizor\SDK\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class EmissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {

        $static = [
            "currency_code" =>1,
            "currency_exchange"=>1
        ];
        $client_phone = '';


        return [

            // STATIC_VALUES

            "codigoMoneda" => $static['currency_code'],
            "tipoCambio" => $static['currency_exchange'],

            // AMOUNTS
            "montoTotalMoneda" => round($this->getMontoTotal(), 2),
            "montoTotal" => round($this->getMontoTotal(), 2),
            "montoTotalSujetoIva" => round($this->getMontoTotalSujetoIva(), 2),
            "montoGiftCard" => round($this->bei_giftcard_amount, 2),
            "descuentoAdicional" => round($this->getDiscount(), 2),

            // CLIENT_DATA
            "nombreRazonSocial" => $this->bei_client["client_business_name"],
            "codigoTipoDocumentoIdentidad" => $this->bei_client["client_document_number_type"],
            "numeroDocumento" => $this->bei_client["client_document_number"],
            "complemento" => $this->bei_client["client_complement"],
            "codigoCliente" => $this->bei_client["client_code"],
            "emailCliente" => $this->bei_client["client_email"]??null,
            "telefonoCliente" => $client_phone,

            // HEADER_DATA
            "numeroFactura" => 1,
            "codigoMetodoPago" => $this->bei_payment_method,
            "numeroTarjeta" => null,
            "usuario" => "USUARIO GENERICO",//$this->usuario,
            "codigoDocumentoSector" => $this->bei_sector_document_id,
            "codigoPuntoVenta" => $this->bei_pos_code,
            "codigoSucursal" => $this->bei_branch_code,
            "extras" => [
                "facturaTicket" => $this->bei_ticket
            ],
            'detalles' => EmissionDetailsResource::collection(collect($this->bei_details)),
            "codigoExcepcion" => $this->bei_exception_code,
        ];
    }
}
