<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Exceptions;

use Throwable;

final class UserAccessException extends \Exception
{
    public function __construct(
        string $message = 'SID was reported being invalid, please check credentials.',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
