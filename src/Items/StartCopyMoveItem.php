<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class StartCopyMoveItem extends AbstractActionItem
{
    public const METHOD = 'start';

    public function getAvailableKeys(): array
    {
        return [
            'path',
            'dest_folder_path',
            'optional' => [
                'overwrite',
                'remove_src',
                'accurate_progress',
                'search_taskid'
            ]
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.CopyMove',
        ];
    }
}
