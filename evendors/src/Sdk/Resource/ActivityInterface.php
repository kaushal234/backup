<?php

declare(strict_types=1);

namespace App\Sdk\Resource;

interface ActivityInterface extends ResourceInterface
{
    final public const COMMENT_TYPE = 'Comment';
    final public const LOG_TYPE = 'Log';

    public function getType(): string;
}
