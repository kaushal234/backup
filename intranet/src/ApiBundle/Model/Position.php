<?php

declare(strict_types=1);

namespace ApiBundle\Model;

class Position
{
    private readonly string $iriId;
    private readonly string $code;

    public function __construct(string $iriId, string $code)
    {
        $this->iriId = $iriId;
        $this->code = $code;
    }

    public function getIriId(): string
    {
        return $this->iriId;
    }

    public function getCode(): string
    {
        return $this->code;
    }
}
