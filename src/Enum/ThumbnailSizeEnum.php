<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Enum;

enum ThumbnailSizeEnum: string
{
    case SMALL = 'small';
    case MEDIUM = 'medium';
    case LARGE = 'large';
    case ORIGINAL = 'original';
}
