<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class QueryItem extends AbstractActionItem
{
    public const METHOD = 'query';

    public function getAvailableKeys(): array
    {
        return [];
    }

    public function getScope(): array
    {
        return [
            'SYNO.API.Info',
        ];
    }
}
