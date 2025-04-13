<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class GetThumbItem extends AbstractActionItem
{
    public const METHOD = 'get';

    public function getAvailableKeys(): array
    {
        return [
            'path',
            'optional' => [
                'size',
                'rotate',
            ],
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.Thumb',
        ];
    }

    public function isDownloadAction(): true
    {
        return true;
    }
}
