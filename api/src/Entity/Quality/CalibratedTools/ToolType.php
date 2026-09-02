<?php

declare(strict_types=1);

namespace App\Entity\Quality\CalibratedTools;

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
use App\Filter\SimpleSearchFilter;
use App\Traits\Quality\CalibratedTools\CountToolsTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ToolType.
 */
#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            uriTemplate: '/quality/calibrated_tools/tool_types',
            normalizationContext: ['groups' => ['tool_type']],
        ),
        new Post(
            uriTemplate: '/quality/calibrated_tools/tool_types',
            security: "is_granted('FEATURE_TOOL_WRITE')",
        ),
        new Get(uriTemplate: '/quality/calibrated_tools/tool_types/{id}'),
        new Put(
            uriTemplate: '/quality/calibrated_tools/tool_types/{id}',
            security: "is_granted('FEATURE_TOOL_WRITE')"
        ),
        new Delete(
            uriTemplate: '/quality/calibrated_tools/tool_types/{id}',
            security: "is_granted('FEATURE_TOOL_WRITE')",
        ),
    ],
    normalizationContext: ['groups' => ['tool_type_detail']],
    denormalizationContext: ['groups' => ['tool_type_write']],
)]
#[ORM\Table(name: 'tool_type')]
#[ApiFilter(SearchFilter::class, properties: ['description' => 'partial', 'tool' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['description' => 'ASC'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['description' => 'partial'])]
#[Gedmo\SoftDeleteable(fieldName: 'deletedAt', timeAware: false)]
class ToolType
{
    use CountToolsTrait;

    #[Groups(['tool', 'tool_detail', 'tool_type', 'tool_type_detail'])]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ApiProperty(iris: ['https://schema.org/text'])]
    #[Groups(['tool', 'tool_detail', 'tool_type', 'tool_type_detail', 'tool_type_write'])]
    #[ORM\Column(name: 'description', type: 'text')]
    #[Assert\NotBlank]
    private string $description;

    /**
     * @var ArrayCollection<Tool>
     */
    #[Groups(['tool_type_detail'])]
    #[ORM\OneToMany(mappedBy: 'toolType', targetEntity: 'App\Entity\Quality\CalibratedTools\Tool')]
    private Collection $tools;

    #[ORM\Column(name: 'deletedAt', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

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
     * Set description.
     *
     * @param string $description
     *
     * @return ToolType
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Add tool.
     *
     * @return ToolType
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
