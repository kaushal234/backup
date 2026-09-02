<?php

declare(strict_types=1);

namespace AppBundle\Service;

class WindowsLineEndingStreamFilter extends \php_user_filter
{
    public function filter($in, $out, &$consumed, $closing): int
    {
        while ($bucket = stream_bucket_make_writeable($in)) {
            $bucket->data = preg_replace('/([^\r])\n$/', "$1\r\n", (string) $bucket->data);
            $consumed += $bucket->datalen;
            stream_bucket_append($out, $bucket);
        }

        return \PSFS_PASS_ON;
    }
}
