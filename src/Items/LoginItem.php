<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Items;

final class LoginItem extends AbstractActionItem
{
    public const METHOD = 'login';

    public function getAvailableKeys(): array
    {
        return [
            'account',
            'passwd',
            'optional' => [
                'session',
                'format',
                'otp_code',
                'enable_syno_token',
                'enable_device_token',
                'device_name',
                'device_id',
            ]
        ];
    }

    public function getScope(): array
    {
        return [
            'SYNO.API.Auth',
        ];
    }
}
