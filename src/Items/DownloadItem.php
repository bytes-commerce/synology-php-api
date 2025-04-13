<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class DownloadItem extends AbstractActionItem
{
    public const METHOD = 'download';

    public function getAvailableKeys(): array
    {
        return [
            'path',
            'mode'
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.Download',
        ];
    }

    public function isDownloadAction(): true
    {
        return true;
    }
}
