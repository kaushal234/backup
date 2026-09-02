<?php

declare(strict_types=1);

namespace App\Client\Response;

class Message
{
    final public const ERROR = 'Error';
    private const NOT_FOUND = 'Object not found.';

    public string $type;
    public string $text;

    public function __construct(string $type, string $text)
    {
        $this->type = $type;
        $this->text = $text;
    }

    public function isError(): bool
    {
        return self::ERROR === $this->type;
    }

    public function isNotFound(): bool
    {
        return self::NOT_FOUND === $this->text;
    }
}
