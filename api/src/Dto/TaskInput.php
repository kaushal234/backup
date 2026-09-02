<?php

declare(strict_types=1);

namespace App\Dto;

use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class TaskInput
{
    #[Assert\NotNull]
    private ?string $resource = null;

    #[Assert\NotBlank]
    #[Assert\NotNull]
    private string $description;

    #[Assert\NotNull]
    private People $assignee;

    private ?\DateTime $dueDate = null;

    private ?int $escalationTrigger = null;

    #[Assert\All([new Assert\Type(type: People::class)])]
    private readonly Collection $cc;

    #[Assert\Type('array')]
    private array $metadata = [];

    public function __construct()
    {
        $this->cc = new ArrayCollection();
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAssignee(): People
    {
        return $this->assignee;
    }

    public function setAssignee(People $assignee): self
    {
        $this->assignee = $assignee;

        return $this;
    }

    public function getDueDate(): ?\DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(?\DateTime $dueDate = null): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getEscalationTrigger(): ?int
    {
        return $this->escalationTrigger;
    }

    public function setEscalationTrigger(int $escalationTrigger): self
    {
        $this->escalationTrigger = $escalationTrigger;

        return $this;
    }

    /**
     * @return Collection<People>
     */
    public function getCc(): Collection
    {
        return $this->cc;
    }

    public function addCc(People $cc): self
    {
        $this->cc->add($cc);

        return $this;
    }

    public function removeCc(People $cc): self
    {
        $this->cc->removeElement($cc);

        return $this;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }
}
