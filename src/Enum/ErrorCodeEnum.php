<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Enum;

enum ErrorCodeEnum: int
{
    use EnumFunctions;

    case UNKNOWN_ERROR = 100;
    case PARAMETERS_MISSING = 101;
    case REQUESTED_API_DOES_NOT_EXIST = 102;
    case REQUESTED_METHOD_NOT_ALLOWED = 103;
    case REQUESTED_VERSION_NOT_SUPPORTED = 104;
    case NO_PERMISSION = 105;
    case SESSION_TIMEOUT = 106;
    case DUPLICATED_LOGIN_INTERRUPTED_SESSION = 107;
    case FAILED_UPLOAD = 108;
    case NETWORK_UNSTABLE_OR_BUSY_109 = 109;
    case NETWORK_UNSTABLE_OR_BUSY_110 = 110;
    case NETWORK_UNSTABLE_OR_BUSY_111 = 111;
    case NETWORK_UNSTABLE_OR_BUSY_112 = 112;
    case PRESERVE_FOR_OTHER_PURPOSE_113 = 113;
    case LOST_PARAMETERS_FOR_API = 114;
    case NOT_ALLOWED_TO_UPLOAD_FILE = 115;
    case NOT_ALLOWED_PERFORM_DEMO_SITE = 116;
    case NETWORK_UNSTABLE_OR_BUSY_117 = 117;
    case NETWORK_UNSTABLE_OR_BUSY_118 = 118;
    case INVALID_SESSION = 119;
    case PRESERVE_FOR_OTHER_PURPOSE_120_149 = 120;
    case REQUEST_SOURCE_IP_MISMATCH = 150;
    case DISABLED_ACCOUNT = 401;
    case DENIED_PERMISSION = 402;
    case TWO_FACTOR_REQUIRED = 403;
    case TWO_FACTOR_CODE_INVALID = 404;
    case TWO_FACTOR_CODE_EXPIRED = 405;
    case TWO_FACTOR_CODE_FORCED = 406;
    case IP_ADDRESS_BLOCKED = 407;
    case EXPIRED_PASSWORD_CANNOT_CHANGE = 408;
    case EXPIRED_PASSWORD = 409;
    case PASSWORD_MUST_BE_CHANGED = 410;

    public function getLabel(): string
    {
        return match ($this) {
            self::UNKNOWN_ERROR => 'Unknown error.',
            self::PARAMETERS_MISSING => 'No parameter of API, method or version.',
            self::REQUESTED_API_DOES_NOT_EXIST => 'The requested API does not exist.',
            self::REQUESTED_METHOD_NOT_ALLOWED => 'The requested method not allowed.',
            self::REQUESTED_VERSION_NOT_SUPPORTED => 'The requested version does not support the functionality.',
            self::NO_PERMISSION => 'The logged in session does not have permission.',
            self::SESSION_TIMEOUT => 'Session timeout.',
            self::DUPLICATED_LOGIN_INTERRUPTED_SESSION => 'Session interrupted by duplicated login.',
            self::FAILED_UPLOAD => 'Failed to upload the file.',
            self::INVALID_SESSION => 'SID not found.',

            self::NETWORK_UNSTABLE_OR_BUSY_109,
            self::NETWORK_UNSTABLE_OR_BUSY_110,
            self::NETWORK_UNSTABLE_OR_BUSY_111,
            self::NETWORK_UNSTABLE_OR_BUSY_117,
            self::NETWORK_UNSTABLE_OR_BUSY_118, => 'The network connection is unstable or the system is busy.',

            self::NETWORK_UNSTABLE_OR_BUSY_112,
            self::PRESERVE_FOR_OTHER_PURPOSE_113,
            self::PRESERVE_FOR_OTHER_PURPOSE_120_149 => 'Preserve for other purpose.',

            self::LOST_PARAMETERS_FOR_API => 'Lost parameters for this API.',
            self::NOT_ALLOWED_TO_UPLOAD_FILE => 'Not allowed to upload a file.',
            self::NOT_ALLOWED_PERFORM_DEMO_SITE => 'Not allowed to perform for a demo site.',
            self::REQUEST_SOURCE_IP_MISMATCH => 'Request source IP does not match the login IP.',

            self::DISABLED_ACCOUNT => 'Account disabled.',
            self::DENIED_PERMISSION => 'Permission denied.',
            self::TWO_FACTOR_REQUIRED => 'Two factor authentication required.',
            self::TWO_FACTOR_CODE_INVALID => 'Two factor authentication code invalid.',
            self::TWO_FACTOR_CODE_EXPIRED => 'Two factor authentication code expired.',
            self::TWO_FACTOR_CODE_FORCED => 'Two factor authentication code forced.',
            self::IP_ADDRESS_BLOCKED => 'IP address blocked.',
            self::EXPIRED_PASSWORD_CANNOT_CHANGE => 'Expired password cannot change.',
            self::EXPIRED_PASSWORD => 'Expired password.',
            self::PASSWORD_MUST_BE_CHANGED => 'Password must be changed.',
        };
    }
}
