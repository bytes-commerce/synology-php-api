<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class StartItem extends AbstractActionItem
{
    public const METHOD = 'start';

    public function getAvailableKeys(): array
    {
        return [
            'path',
            'optional' => [
                'accurate_progress',
                'recursive',
                'search_taskid'
            ]
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.Delete',
        ];
    }
}
