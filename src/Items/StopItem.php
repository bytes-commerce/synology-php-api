<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class StopItem extends AbstractActionItem
{
    public const METHOD = 'stop';

    public function getAvailableKeys(): array
    {
        return [
            'taskid',
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.Delete',
        ];
    }
}
