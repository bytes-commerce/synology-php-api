<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class RenameItem extends AbstractActionItem
{
    public const METHOD = 'rename';

    public function getAvailableKeys(): array
    {
        return [
            'path',
            'name',
            'optional' => [
                'additional',
                'search_taskid'
            ]
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.Rename',
        ];
    }
}
