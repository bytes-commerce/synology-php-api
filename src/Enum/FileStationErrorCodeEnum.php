<?php

declare(strict_types=1);

namespace BytesCommerce\SynologyApi\Enum;

enum FileStationErrorCodeEnum: int
{
    use EnumFunctions;

    case INVALID_PARAMETER_OF_FILE_OPERATION = 400;
    case UNKNOWN_ERROR_OF_FILE_OPERATION = 401;
    case SYSTEM_TOO_BUSY = 402;
    case INVALID_USER_ID = 403;
    case INVALID_GROUP_FOR_FILE_OPERATION = 404;
    case USER_GROUP_INFO_NOT_FOUND = 405;
    case OPERATION_NOT_PERMITTED = 406;
    case NO_SUCH_FILE_OR_DIRECTORY = 407;
    case NON_SUPPORTED_FILE_SYSTEM = 408;
    case FAILED_TO_CONNECT_INTERNET_BASED_FILE_SYSTEM = 409;
    case READ_ONLY_FILE_SYSTEM = 410;
    case FILENAME_TOO_LONG_NON_ENCRYPTED = 411;
    case FILENAME_TOO_LONG_ENCRYPTED = 412;
    case FILE_ALREADY_EXISTS = 413;
    case DISK_QUOTA_EXCEEDED = 414;
    case NO_SPACE_LEFT_ON_DEVICE = 415;
    case INPUT_OUTPUT_ERROR = 416;
    case ILLEGAL_NAME_OR_PATH = 417;
    case ILLEGAL_FILE_NAME = 418;
    case ILLEGAL_FILE_NAME_ON_FAT_FILE_SYSTEM = 419;
    case NO_SUCH_RESOURCE_OR_DEVICE_BUSY = 420;
    case NO_SUCH_TASK_OF_FILE_OPERATION = 421;

    public function getLabel(): string
    {
        return match ($this) {
            self::INVALID_PARAMETER_OF_FILE_OPERATION => 'Invalid parameter of file operation',
            self::UNKNOWN_ERROR_OF_FILE_OPERATION => 'Unknown error of file operation',
            self::SYSTEM_TOO_BUSY => 'System is too busy',
            self::INVALID_USER_ID => 'Invalid user ID',
            self::INVALID_GROUP_FOR_FILE_OPERATION => 'Invalid group does this file operation',
            self::USER_GROUP_INFO_NOT_FOUND => "Can't get user/group information from the account server",
            self::OPERATION_NOT_PERMITTED => 'Operation not permitted',
            self::NO_SUCH_FILE_OR_DIRECTORY => 'No such file or directory',
            self::NON_SUPPORTED_FILE_SYSTEM => 'Non-supported file system',
            self::FAILED_TO_CONNECT_INTERNET_BASED_FILE_SYSTEM => 'Failed to connect internet-based file system (e.g., CIFS)',
            self::READ_ONLY_FILE_SYSTEM => 'Read-only file system',
            self::FILENAME_TOO_LONG_NON_ENCRYPTED => 'Filename too long in the non-encrypted file system',
            self::FILENAME_TOO_LONG_ENCRYPTED => 'Filename too long in the encrypted file system',
            self::FILE_ALREADY_EXISTS => 'File already exists',
            self::DISK_QUOTA_EXCEEDED => 'Disk quota exceeded',
            self::NO_SPACE_LEFT_ON_DEVICE => 'No space left on device',
            self::INPUT_OUTPUT_ERROR => 'Input/output error',
            self::ILLEGAL_NAME_OR_PATH => 'Illegal name or path',
            self::ILLEGAL_FILE_NAME => 'Illegal file name',
            self::ILLEGAL_FILE_NAME_ON_FAT_FILE_SYSTEM => 'Illegal file name on FAT file system',
            self::NO_SUCH_RESOURCE_OR_DEVICE_BUSY => 'No such resource or device busy',
            self::NO_SUCH_TASK_OF_FILE_OPERATION => 'No such task of the file operation',
        };
    }
}
