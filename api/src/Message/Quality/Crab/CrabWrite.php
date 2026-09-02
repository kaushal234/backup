<?php

declare(strict_types=1);

namespace App\Message\Quality\Crab;

use App\Message\MessageTrait;

class CrabWrite
{
    use MessageTrait {
        MessageTrait::__construct as private __traitConstruct;
    }

    private readonly string $method;

    public function __construct(string $userIri, string $resourceIri, string $method)
    {
        $this->__traitConstruct($userIri, $resourceIri);
        $this->method = $method;
    }

    public function getMethod(): string
    {
        return $this->method;
    }
}
