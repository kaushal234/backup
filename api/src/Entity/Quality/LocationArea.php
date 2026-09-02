<?php

declare(strict_types=1);

namespace App\Entity\Quality;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Quality\CalibratedTools\Tool;
use App\Filter\SimpleSearchFilter;
use App\Traits\Quality\CalibratedTools\CountToolsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Location.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['location_areas', 'people_public']]),
        new Post(security: "is_granted('FEATURE_TOOL_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_TOOL_WRITE')"),
        new Delete(security: "is_granted('FEATURE_TOOL_WRITE')"),
    ],
    normalizationContext: ['groups' => ['location_areas_detail', 'people_public']],
    denormalizationContext: ['groups' => ['location_areas', 'location_areas_detail']],
)]
#[ORM\Table(name: 'location_area')]
#[ApiFilter(OrderFilter::class)]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'partial', 'factory' => 'exact', 'supervisor' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[App\Loggable]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class LocationArea
{
    use CountToolsTrait;

    #[Groups(['location_areas', 'location_areas_detail', 'tool', 'tool_detail', 'tool_type'])]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['location_areas', 'location_areas_detail', 'tool', 'tool_detail', 'tool_type'])]
    #[ORM\Column(name: 'name', type: 'string', length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private string $name;

    /**
     * Many location area have one Location.
     */
    #[Groups(['location_areas', 'location_areas_detail', 'tool', 'tool_detail'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location', inversedBy: 'locationAreas')]
    #[ORM\JoinColumn(name: 'factory_id', referencedColumnName: 'id')]
    #[Assert\NotBlank]
    private ?Location $factory = null;

    /**
     * Many location areas have one supervisor.
     */
    #[Groups(['location_areas', 'location_areas_detail', 'tool', 'tool_detail'])]
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'supervisor_id', referencedColumnName: 'id')]
    #[Assert\NotBlank]
    private ?People $supervisor = null;

    /**
     * @var Collection<Tool>
     */
    #[Groups(['location_areas_detail'])]
    #[ORM\OneToMany(mappedBy: 'locationArea', targetEntity: 'App\Entity\Quality\CalibratedTools\Tool')]
    private Collection $tools;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->tools = new ArrayCollection();
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return LocationArea
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add tool.
     *
     * @return LocationArea
     */
    public function addTool(Tool $tool)
    {
        $this->tools[] = $tool;

        return $this;
    }

    /**
     * Remove tool.
     */
    public function removeTool(Tool $tool)
    {
        $this->tools->removeElement($tool);
    }

    /**
     * Get tools.
     *
     * @return Collection<Tool>
     */
    public function getTools()
    {
        return $this->tools;
    }

    /**
     * Set factory.
     *
     * @return LocationArea
     */
    public function setFactory(?Location $factory = null)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Get factory.
     *
     * @return Location
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * @return $this
     */
    public function setSupervisor(?People $supervisor = null)
    {
        $this->supervisor = $supervisor;

        return $this;
    }

    /**
     * @return People
     */
    public function getSupervisor()
    {
        return $this->supervisor;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * @param \DateTime $deletedAt
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;
    }
}
