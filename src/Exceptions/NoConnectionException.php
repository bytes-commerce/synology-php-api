<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Exceptions;

use Throwable;

final class NoConnectionException extends \Exception
{
    public function __construct(
        string $message = 'Cannot establish connection to NAS. Please check the connection parameters or try again later.',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
