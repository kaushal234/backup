<?php

declare(strict_types=1);

namespace ApiBundle\Model;

class BusinessUnit
{
    private readonly string $iriId;

    private readonly string $iriType;

    private readonly int $id;

    private readonly string $name;

    public function __construct(string $iriId, string $iriType, int $id, string $name)
    {
        $this->iriId = $iriId;
        $this->iriType = $iriType;
        $this->id = $id;
        $this->name = $name;
    }

    public function getIriId(): string
    {
        return $this->iriId;
    }

    public function getIriType(): string
    {
        return $this->iriType;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }
}
