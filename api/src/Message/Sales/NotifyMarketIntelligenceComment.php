<?php

declare(strict_types=1);

namespace App\Message\Sales;

use App\Message\MessageTrait;

class NotifyMarketIntelligenceComment
{
    use MessageTrait {
        MessageTrait::__construct as private __traitConstruct;
    }

    private readonly string $comment;

    public function __construct(string $userIri, string $resourceIri, string $comment)
    {
        $this->__traitConstruct($userIri, $resourceIri);
        $this->comment = $comment;
    }

    public function getComment(): string
    {
        return $this->comment;
    }
}
