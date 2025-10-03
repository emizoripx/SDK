<?php

namespace Emizor\SDK\Builders;

use Closure;
use Emizor\SDK\DTO\ClientDTO;
use Emizor\SDK\DTO\InvoiceDetailDTO;
use Emizor\SDK\DTO\InvoiceDTO;

class InvoiceBuilder
{
    protected ClientDTO $client;
    protected array $details = [];
    protected array $defaults = [];
    protected float $amount = 0.00;
    protected float $discount = 0.00;
    protected ?string $ticket = null;
    public function __construct(string $ticket, array $defaults = [])
    {
        $this->defaults = $defaults;
        $this->ticket = $ticket;
    }

    public function setClient(ClientDTO $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function setDetails(array $details): self
    {
        foreach ($details as $d) {
            $this->details[] = new InvoiceDetailDTO(
                $d["product_code"]??"",
                $d["description"]??"",
                $d["quantity"]??0,
                $d["unit_price"]??0,
                $d["unit_code"]??"",
            );
        }
        return $this;
    }

    public function setTypeDocument(string $type): self
    {
        $this->defaults['type_document'] = $type;
        return $this;
    }

    public function setBranch(string $branch): self
    {
        $this->defaults['branch'] = $branch;
        return $this;
    }

    public function setPos(string $pos): self
    {
        $this->defaults['pos'] = $pos;
        return $this;
    }

    public function setPaymentMethod(string $method): self
    {
        $this->defaults['payment_method'] = $method;
        return $this;
    }

    public function setReasonRevocation(string $reason): self
    {
        $this->defaults['reason_revocation'] = $reason;
        return $this;
    }

    public function setAmount($amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function setDiscount($discount): self
    {
        $this->discount = $discount;
        return $this;
    }

    public function build(): InvoiceDTO
    {
        return new InvoiceDTO(
            $this->ticket,
            $this->client?->toArray(),
            array_map(fn($d) => $d->toArray(), $this->details),
            $this->defaults["type_document"]??null,
            $this->defaults["branch"]??null,
            $this->defaults["pos"]??null,
            $this->defaults["payment_method"]??null,
            $this->discount??0,
            $this->amount??0,
        );
    }

}
