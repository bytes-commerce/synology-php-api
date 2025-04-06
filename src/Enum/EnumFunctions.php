<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Enum;

trait EnumFunctions
{
    public static function has(int $code): bool
    {
        /** @phpstan-ignore-next-line  */
        return in_array($code, array_column(self::cases(), 'value'), true);
    }
}
