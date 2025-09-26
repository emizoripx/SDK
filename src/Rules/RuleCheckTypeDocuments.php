<?php

namespace Emizor\SDK\Rules;

use Emizor\SDK\Enums\InvoiceType;
use Illuminate\Contracts\Validation\Rule;

class RuleCheckTypeDocuments implements Rule
{
    protected string $attribute;

    public function passes($attribute, $value): bool
    {
        $this->attribute = $attribute;

        // Convierte los casos en un array de valores string
        $allowed = array_map(fn($case) => $case->value, InvoiceType::cases());

        return in_array($value, $allowed, true);
    }

    public function message(): string
    {
        $allowed = array_map(fn($case) => $case->value, InvoiceType::cases());

        return "El campo {$this->attribute} debe ser uno de: " . implode(', ', $allowed);
    }
}
