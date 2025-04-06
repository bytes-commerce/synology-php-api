<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class LogoutItem extends AbstractActionItem
{
    public const METHOD = 'logout';

    public function getAvailableKeys(): array
    {
        return [];
    }

    public function getScope(): array
    {
        return [
            'SYNO.API.Auth',
        ];
    }
}
