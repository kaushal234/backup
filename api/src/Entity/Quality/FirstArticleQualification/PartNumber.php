<?php

declare(strict_types=1);

namespace App\Entity\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    shortName: 'firstArticleQualificationsPartNumber',
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'quality',
    normalizationContext: ['groups' => ['faq_part_number']],
    denormalizationContext: ['groups' => ['faq_part_number_write']],
    forceEager: false
)]
#[ORM\Table(name: 'first_article_qualifications_part_numbers')]
class PartNumber
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Quality\FirstArticleQualification\FirstArticleQualification', inversedBy: 'partNumbers')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['faq_part_number_write'])]
    private FirstArticleQualification $firstArticleQualification;

    #[ORM\Column(type: 'string', length: 32, nullable: false)]
    #[Assert\Length(max: 32)]
    #[Assert\NotNull]
    #[Groups(['faq_part_number', 'faq_part_number_write'])]
    private string $number;

    #[ORM\Column(type: 'string', length: 6, nullable: false)]
    #[Assert\Length(max: 6)]
    #[Assert\NotNull]
    #[Groups(['faq_part_number', 'faq_part_number_write'])]
    private string $revision;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\Length(max: 255)]
    #[Groups(['faq_part_number', 'faq_part_number_write'])]
    private string $description = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function getFirstArticleQualification(): FirstArticleQualification
    {
        return $this->firstArticleQualification;
    }

    public function setFirstArticleQualification(FirstArticleQualification $firstArticleQualification): self
    {
        $this->firstArticleQualification = $firstArticleQualification;

        return $this;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

        return $this;
    }

    public function getRevision(): string
    {
        return $this->revision;
    }

    public function setRevision(string $revision): self
    {
        $this->revision = $revision;

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
}
