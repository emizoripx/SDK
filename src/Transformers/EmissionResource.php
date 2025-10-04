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
        return [
            // STATIC VALUES
            "codigoMoneda" => 1,
            "tipoCambio" => 1,

            // AMOUNTS
            "montoTotalMoneda" => round($this->getMontoTotal(), 2),
            "montoTotal" => round($this->getMontoTotal(), 2),
            "montoTotalSujetoIva" => round($this->getMontoTotalSujetoIva(), 2),
            "montoGiftCard" => round($this->bei_giftcard_amount, 2),
            "descuentoAdicional" => round($this->getDiscountAmount(), 2),

            // CLIENT DATA
            "nombreRazonSocial" => $this->bei_client["client_business_name"],
            "codigoTipoDocumentoIdentidad" => $this->bei_client["client_document_number_type"],
            "numeroDocumento" => $this->bei_client["client_document_number"],
            "complemento" => $this->bei_client["client_complement"],
            "codigoCliente" => $this->bei_client["client_code"],
            "emailCliente" => $this->bei_client["client_email"],
            "telefonoCliente" => '',

            // HEADER DATA
//            "numeroFactura"=>1,
            "codigoMetodoPago" => $this->bei_payment_method,
            "numeroTarjeta" => null,
            "usuario" => "USUARIO GENERICO",
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

    /**
     * Get discount amount safely
     */
    private function getDiscountAmount(): float
    {
        // Check if discount properties exist
        if (property_exists($this, 'discount') && property_exists($this, 'is_amount_discount')) {
            if ($this->is_amount_discount) {
                return $this->discount;
            }
            return $this->discount * $this->getSubtotal() / 100;
        }
        return 0.0;
    }
}
