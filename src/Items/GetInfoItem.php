<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class GetInfoItem extends AbstractActionItem
{
    public const METHOD = 'getinfo';

    public function getAvailableKeys(): array
    {
        return [
            'path',
            'optional' => [
                'additional'
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
