<?php

declare(strict_types=1);

namespace App\Message;

trait MessageTrait
{
    private string $resourceIri;

    private string $userIri;

    public function __construct(string $userIri, string $resourceIri)
    {
        $this->userIri = $userIri;
        $this->resourceIri = $resourceIri;
    }

    public function getResourceIri(): string
    {
        return $this->resourceIri;
    }

    public function getUserIri(): string
    {
        return $this->userIri;
    }
}
