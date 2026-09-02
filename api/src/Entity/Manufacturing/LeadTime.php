<?php

declare(strict_types=1);

namespace App\Entity\Manufacturing;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Sales\ProductFamily;
use App\Repository\Manufacturing\LeadTimeRepository;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LeadTimeRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Delete(security: "is_granted('LEAD_TIME_WRITE_VOTER', object)"),
    ],
    normalizationContext: ['groups' => ['lead_time', 'location_public', 'catalogue_public', 'people_public']],
    denormalizationContext: ['groups' => ['lead_time:write']],
)]
#[ORM\Table(name: 'lead_times')]
#[ORM\UniqueConstraint(name: 'unique_product_family_per_factory', columns: ['factory_id', 'product_family_id'])]
#[ApiFilter(SearchFilter::class, properties: ['factory', 'productFamily', 'productFamily.productType', 'productFamily.hidden'])]
#[Loggable]
class LeadTime
{
    #[ORM\Column(type: 'smallint')]
    #[Assert\NotNull]
    #[Groups(['lead_time', 'lead_time:write'])]
    public int $weeks;

    #[ORM\Column(type: 'smallint', nullable: true)]
    #[Groups(['lead_time'])]
    public ?int $previousValue = null;

    #[ORM\Column(type: 'string', length: 1000)]
    #[Assert\Length(max: 1000)]
    #[Assert\NotBlank]
    #[Groups(['lead_time', 'lead_time:write'])]
    public string $description;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Sales\ProductFamily')]
    #[Assert\NotNull]
    #[Groups(['lead_time', 'lead_time:write'])]
    public ProductFamily $productFamily;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[Assert\NotNull]
    #[Groups(['lead_time', 'lead_time:write'])]
    #[ValidLocation(factory: true)]
    public Location $factory;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(name: 'updated_by', referencedColumnName: 'id', nullable: true)]
    #[Groups(['lead_time'])]
    #[Gedmo\Blameable(on: 'update')]
    public ?People $updatedBy = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(['lead_time'])]
    #[Gedmo\Timestampable(on: 'update')]
    public ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
