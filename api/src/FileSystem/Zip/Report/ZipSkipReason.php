<?php

declare(strict_types=1);

namespace App\FileSystem\Zip\Report;

enum ZipSkipReason: string
{
    case FILE_NOT_FOUND = 'file_not_found';
    case PERMISSION_DENIED = 'permission_denied';
    case NOT_A_FILE = 'not_a_file';
    case UNSUPPORTED_FORMAT = 'unsupported_format';
}
