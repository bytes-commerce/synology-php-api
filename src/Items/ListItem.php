<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class ListItem extends AbstractActionItem
{
    public const METHOD = 'list';

    public function getAvailableKeys(): array
    {
        return [
            'folder_path',
            'optional' => [
                'offset',
                'limit',
                'pattern',
                'sort_by',
                'sort_direction',
                'pattern',
                'filetype',
                'goto_path',
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
