<?php
namespace Emizor\SDK\Builders;

use Emizor\SDK\DTO\DefaultsDTO;

class DefaultsBuilder
{
    protected array $data = [];

    public function setTypeDocument(string $type = null): self
    {
        $this->data['type_document'] = $type;
        return $this;
    }

    public function setBranch(string $branch=null): self
    {
        $this->data['branch'] = $branch;
        return $this;
    }

    public function setPos(string $pos=null): self
    {
        $this->data['pos'] = $pos;
        return $this;
    }

    public function setPaymentMethod(string $method=null): self
    {
        $this->data['payment_method'] = $method;
        return $this;
    }

    public function setReasonRevocation(string $reason=null): self
    {
        $this->data['reason_revocation'] = $reason;
        return $this;
    }

    public function build(): DefaultsDTO
    {
        // Al crear el DTO, se activará la validación en su constructor.
        return new DefaultsDTO(
            $this->data['type_document'] ?? null,
            $this->data['branch'] ?? null,
            $this->data['pos'] ?? null,
            $this->data['payment_method'] ?? null,
            $this->data['reason_revocation'] ?? null,
        );
    }
}
