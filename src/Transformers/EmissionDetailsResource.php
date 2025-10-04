<?php

namespace Emizor\SDK\Transformers;

use Illuminate\Http\Resources\Json\JsonResource;

class EmissionDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @return array
     */
    public function toArray($request)
    {
        $subtotal = round((float)$this->resource["unit_price"] * (float)$this->resource["quantity"], 2);

        return [
            "codigoProducto" => $this->resource["product_code"],

            "codigoProductoSin" => $this->resource["bei_sin_product_code"],
            "codigoActividadSin" => $this->resource["bei_activity_code"],

            "descripcion" => $this->resource["description"],
            "cantidad" => $this->resource["quantity"],
            "precioUnitario" => $this->resource["unit_price"],
            "subTotal" => $subtotal,
            "montoDescuento" => $this->resource["discount"] ?? 0,
            "unidadMedida" => $this->resource["unit_code"],
        ];
    }
}
