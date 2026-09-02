<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\PremiseTag;
use App\Entity\MIS\Project\ProjectTag;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualificationTag;
use App\Entity\Sales\ProductFamilyTag;
use App\Entity\Service\TechnicianOnCallTag;
use App\Filter\DiscriminatorFilter;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'discr', type: 'string')]
#[ORM\DiscriminatorMap([
    'faq' => FirstArticleQualificationTag::class,
    'premise' => PremiseTag::class,
    'product_family' => ProductFamilyTag::class,
    'project' => ProjectTag::class,
    'toc' => TechnicianOnCallTag::class,
])]
#[ApiResource(
    operations: [
        new GetCollection(uriTemplate: 'tags'),
        new Get(
            uriTemplate: 'tags/{id}',
            normalizationContext: ['groups' => ['tag_detail']],
        ),
    ],
    normalizationContext: ['groups' => ['tag']],
    denormalizationContext: ['groups' => ['tag_write']]
)]
#[ORM\Table(name: 'tags')]
#[ORM\UniqueConstraint(name: 'unique_tag_per_resource_type', columns: ['name', 'discr'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'exact'])]
#[ApiFilter(OrderFilter::class, properties: ['name'])]
#[ApiFilter(DiscriminatorFilter::class)]
abstract class AbstractTag implements \Stringable
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\Column(name: 'name', type: 'string')]
    #[Groups(['tag', 'tag_detail', 'tag_write'])]
    #[Assert\NotBlank]
    private string $name;

    #[ORM\Column(name: 'created_at', type: 'datetime')]
    #[Groups(['tag_detail'])]
    #[Gedmo\Timestampable(on: 'create')]
    private \DateTimeInterface $createdAt;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\User')]
    #[ORM\JoinColumn(name: 'created_by', referencedColumnName: 'id', nullable: true)]
    #[Groups(['tag_detail'])]
    #[Gedmo\Blameable(on: 'create')]
    private ?User $createdBy = null;

    public function __toString(): string
    {
        return $this->name;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    public function getCreatedBy(): ?User
    {
        return $this->createdBy;
    }

    public function setCreatedBy(User $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }
}
