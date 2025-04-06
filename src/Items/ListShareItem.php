<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class ListShareItem extends AbstractActionItem
{
    public const METHOD = 'list_share';

    public function getAvailableKeys(): array
    {
        return [
            'optional' => [
                'offset',
                'limit',
                'sort_by',
                'sort_direction',
                'onlywritable',
                'additional',
            ]
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.List',
        ];
    }
}
