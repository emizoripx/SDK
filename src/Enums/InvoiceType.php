<?php

namespace Emizor\SDK\Enums;

use InvalidArgumentException;

enum InvoiceType: string
{
    case COMPRA_VENTA = "1";

    public static function getActionList(): array
    {
        return [
            self::COMPRA_VENTA,

        ];
    }

    public function getValue(): int
    {
        return match ($this) {
            self::COMPRA_VENTA => 1,

        };
    }

    public static function getName(int $value): string
    {
        return match ($value) {
            1 => "compra-venta",
            default => throw new InvalidArgumentException("Invalid action type value: $value")
        };
    }

    public function toString(): string
    {
        return match ($this) {
            self::COMPRA_VENTA => "compra-venta",

        };
    }
}
