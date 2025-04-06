<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class TokenItem extends AbstractActionItem
{
    public const METHOD = 'login';

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
