<?php

declare(strict_types=1);

namespace App\Entity\Materials\Warehouse;

use ApiPlatform\Doctrine\Orm\Filter\NumericFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\Location;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['tasks_mapping', 'location_public']]),
        new Post(
            denormalizationContext: ['groups' => ['tasks_mapping:write', 'tasks_mapping:create']],
            securityPostDenormalize: "is_granted('TASKS_MAPPING_WRITE_VOTER', object)",
        ),
        new Get(),
        new Put(security: "is_granted('TASKS_MAPPING_WRITE_VOTER', object)"),
    ],
    routePrefix: 'materials/warehouse',
    normalizationContext: ['groups' => ['tasks_mapping:detail', 'location_public']],
    denormalizationContext: ['groups' => ['tasks_mapping:write']],
)]
#[ORM\Entity]
#[UniqueEntity(fields: ['location'])]
#[ORM\Table(name: 'warehouse_tasks_mappings')]
#[ORM\UniqueConstraint(name: 'unique_location', columns: ['location_id'])]
#[ApiFilter(NumericFilter::class, properties: ['location.erp'])]
class TasksMapping
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['tasks_mapping:detail'])]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['tasks_mapping', 'tasks_mapping:detail', 'tasks_mapping:create'])]
    #[ValidLocation(warehouse: true, erpInLN: true)]
    private Location $location;

    #[ORM\Column(type: 'simple_array', length: 255)]
    #[Assert\Count(min: 1, max: 50)]
    #[Assert\All([
        new Assert\Type(type: 'string'),
        new Assert\Regex(pattern: "/^(\d){1,4}$/", message: 'This value should be an integer between 1 and 9999.')]
    )]
    #[Groups(['tasks_mapping', 'tasks_mapping:detail', 'tasks_mapping:write'])]
    private array $inboundTasks = [];

    #[ORM\Column(type: 'simple_array', length: 255)]
    #[Assert\Count(min: 1, max: 50)]
    #[Assert\All([
        new Assert\Type(type: 'string'),
        new Assert\Regex(pattern: "/^(\d){1,4}$/", message: 'This value should be an integer between 1 and 9999.')]
    )]
    #[Groups(['tasks_mapping', 'tasks_mapping:detail', 'tasks_mapping:write'])]
    private array $outboundTasks = [];

    #[ORM\Column(type: 'simple_array', length: 255)]
    #[Assert\Count(min: 1, max: 50)]
    #[Assert\All([
        new Assert\Type(type: 'string'),
        new Assert\Regex(pattern: "/^(\d){1,4}$/", message: 'This value should be an integer between 1 and 9999.')]
    )]
    #[Groups(['tasks_mapping', 'tasks_mapping:detail', 'tasks_mapping:write'])]
    private array $administrativeTasks = [];

    #[ORM\Column(type: 'simple_array', length: 255)]
    #[Assert\Count(min: 0, max: 50)]
    #[Assert\All([
        new Assert\Type(type: 'string'),
        new Assert\Regex(pattern: "/^(\d){1,4}$/", message: 'This value should be an integer between 1 and 9999.')]
    )]
    #[Groups(['tasks_mapping', 'tasks_mapping:detail', 'tasks_mapping:write'])]
    private array $excludedTasks = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getInboundTasks(): array
    {
        return $this->inboundTasks;
    }

    public function setInboundTasks(array $inboundTasks): self
    {
        $this->inboundTasks = $inboundTasks;

        return $this;
    }

    public function getOutboundTasks(): array
    {
        return $this->outboundTasks;
    }

    public function setOutboundTasks(array $outboundTasks): self
    {
        $this->outboundTasks = $outboundTasks;

        return $this;
    }

    public function getAdministrativeTasks(): array
    {
        return $this->administrativeTasks;
    }

    public function setAdministrativeTasks(array $administrativeTasks): self
    {
        $this->administrativeTasks = $administrativeTasks;

        return $this;
    }

    public function getExcludedTasks(): array
    {
        return $this->excludedTasks;
    }

    public function setExcludedTasks(array $administrativeTasks): self
    {
        $this->excludedTasks = $administrativeTasks;

        return $this;
    }

    public function getAllTasks(): array
    {
        return array_merge($this->inboundTasks, $this->outboundTasks, $this->administrativeTasks, $this->excludedTasks);
    }

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context)
    {
        if ([] !== array_intersect($this->outboundTasks, $this->inboundTasks)) {
            $context->buildViolation('At least one task is already used as inbound task.')
                ->atPath('outboundTasks')
                ->addViolation();
        }

        if ([] !== array_intersect($this->administrativeTasks, $this->inboundTasks)) {
            $context->buildViolation('At least one task is already used as inbound task.')
                ->atPath('administrativeTasks')
                ->addViolation();
        }

        if ([] !== array_intersect($this->administrativeTasks, $this->outboundTasks)) {
            $context->buildViolation('At least one task is already used as outbound task.')
                ->atPath('administrativeTasks')
                ->addViolation();
        }

        if ([] !== array_intersect($this->excludedTasks, $this->inboundTasks)) {
            $context->buildViolation('At least one task is already used as inbound task.')
                ->atPath('excludedTasks')
                ->addViolation();
        }

        if ([] !== array_intersect($this->excludedTasks, $this->outboundTasks)) {
            $context->buildViolation('At least one task is already used as outbound task.')
                ->atPath('excludedTasks')
                ->addViolation();
        }

        if ([] !== array_intersect($this->excludedTasks, $this->administrativeTasks)) {
            $context->buildViolation('At least one task is already used as administrative task.')
                ->atPath('excludedTasks')
                ->addViolation();
        }
    }
}
