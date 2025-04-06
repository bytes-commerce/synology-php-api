<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class StatusItem extends AbstractActionItem
{
    public const METHOD = 'status';

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
