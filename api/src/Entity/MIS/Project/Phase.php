<?php

declare(strict_types=1);

namespace App\Entity\MIS\Project;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Symfony\Action\NotFoundAction;
use App\DataProcessor\MIS\Project\PhaseTaskDataProcessor;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Task\Task;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Table(name: 'phases')]
#[ORM\Entity]
#[ApiResource(
    operations: [
        new Get(
            controller: NotFoundAction::class,
        ),
        new Put(
            denormalizationContext: ['groups' => ['phase_task:write', 'task:write', 'base_task:write']],
            input: Task::class,
            name: 'update_project_phase',
            processor: PhaseTaskDataProcessor::class
        ),
    ],
    routePrefix: 'mis',
)]
#[App\Loggable(owner: 'project', ownerRelation: 'phases')]
class Phase implements \Stringable
{
    #[ORM\Column(type: 'integer')]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['phase', 'phase:create'])]
    public int $number;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Assert\NotNull]
    #[Groups(['phase', 'phase:create', 'phase:estimated_date'])]
    public ?\DateTimeInterface $estimatedClosureAt = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(['phase', 'phase:create', 'phase:revised_date'])]
    public ?\DateTimeInterface $revisedClosureAt = null;

    #[ORM\Column(type: 'integer')]
    #[Assert\NotNull]
    #[Groups(['phase', 'phase:create', 'phase:edit'])]
    public int $estimatedHours;

    #[ORM\Column(type: 'integer', nullable: true)]
    #[Groups(['phase', 'phase:edit'])]
    public ?int $revisedEstimatedHours = null;

    #[ORM\ManyToOne(targetEntity: Project::class, inversedBy: 'phases')]
    public Project $project;

    #[ORM\ManyToMany(targetEntity: Task::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    #[Groups(['phase', 'phase_task:write'])]
    private Collection $tasks;

    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[ORM\Column(type: 'integer')]
    private int $id;

    public function __construct()
    {
        $this->tasks = new ArrayCollection();
    }

    public function __toString(): string
    {
        return \constant(\sprintf('App\Entity\MIS\Project\Project::PHASE_%s', $this->number));
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isActive(): bool
    {
        return (string) $this === $this->project->getStatus();
    }

    /**
     * @return Collection<Task>
     */
    public function getTasks(): Collection
    {
        return $this->tasks;
    }

    public function addTask(Task $task): self
    {
        if (!$this->tasks->contains($task)) {
            $this->tasks->add($task);
        }

        return $this;
    }

    public function removeTask(Task $task): self
    {
        if ($this->tasks->contains($task)) {
            $this->tasks->removeElement($task);
        }

        return $this;
    }
}
