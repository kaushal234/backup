<?php

declare(strict_types=1);

namespace LegacyBundle\Model;

use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

class Task
{
    #[Assert\NotBlank]
    protected string $module = '';

    protected ?int $parentId = null;

    #[Assert\NotNull]
    protected People $assignee;

    #[Assert\NotNull]
    protected People $assignor;

    #[Assert\NotNull]
    #[Assert\NotBlank]
    protected string $description;

    #[Assert\NotNull]
    protected Location $location;

    #[Assert\NotNull]
    protected \DateTime $date;

    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual('today')]
    protected \DateTime $dueDate;

    protected ?int $escalationTrigger = 60;
    private int $id;

    private readonly Collection $cc;

    public function __construct()
    {
        $this->date = new \DateTime();
        $this->dueDate = new \DateTime('+14 days');
        $this->cc = new ArrayCollection();
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getModule(): string
    {
        return $this->module;
    }

    public function setModule(string $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function setParentId(int $parentId): self
    {
        $this->parentId = $parentId;

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

    public function getAssignor(): People
    {
        return $this->assignor;
    }

    public function setAssignor(People $assignor): self
    {
        $this->assignor = $assignor;

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

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getDueDate(): \DateTime
    {
        return $this->dueDate;
    }

    public function setDueDate(\DateTime $dueDate): self
    {
        $this->dueDate = $dueDate;

        return $this;
    }

    public function getEscalationTrigger(): ?int
    {
        return $this->escalationTrigger;
    }

    public function setEscalationTrigger(?int $escalationTrigger = null): self
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
        if (!$this->getCc()->contains($cc)) {
            $this->cc->add($cc);
        }

        return $this;
    }

    public function removeCc(People $cc): self
    {
        $this->cc->removeElement($cc);

        return $this;
    }
}
