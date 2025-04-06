<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

use Webmozart\Assert\Assert;

final class UploadItem extends AbstractActionItem
{
    public const METHOD = 'upload';

    public const IS_MULTIPART_FORM = true;

    public function getAvailableKeys(): array
    {
        return [
            'path',
            'create_parents',
            'overwrite',
            'optional' => [
                'mtime',
                'crtime',
                'atime',
                'additional',
            ],
            'filename',
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.FileStation.Upload',
        ];
    }

    public function parameterOrderValidator(array $params): bool
    {
        Assert::notFalse($params['filename']);
        Assert::false(empty($params['path']));

        return true;
    }
}
