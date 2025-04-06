<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class CreateItem extends AbstractActionItem
{
    public const METHOD = 'create';

    public function getAvailableKeys(): array
    {
        return [
            'folder_path',
            'name',
            'force_parent',
            'optional' => [
                'force_parent',
                'additional'
            ]
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.CreateFolder',
        ];
    }
}
