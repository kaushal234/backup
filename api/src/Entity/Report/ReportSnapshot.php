<?php

declare(strict_types=1);

namespace App\Entity\Report;

use ApiPlatform\Doctrine\Orm\Filter\DateFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: 'App\Repository\Report\ReportSnapshotRepository')]
#[ApiResource(
    operations: [
        new Get(),
        new GetCollection(),
    ],
    normalizationContext: ['groups' => ['report_snapshot']]
)]
#[ORM\Table]
#[ApiFilter(SearchFilter::class, properties: ['resource' => 'exact', 'x' => 'exact', 'y' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
class ReportSnapshot implements \Stringable
{
    #[ORM\Column(type: 'datetime')]
    #[Groups(['report_snapshot'])]
    public \DateTimeInterface $createdAt;

    #[ORM\Column(type: 'string')]
    #[Groups(['report_snapshot'])]
    public string $resource;

    #[ORM\Column(type: 'string')]
    #[Groups(['report_snapshot'])]
    public string $x;

    #[ORM\Column(type: 'string')]
    #[Groups(['report_snapshot'])]
    public string $y;

    #[ORM\Column(type: 'json')]
    #[Groups(['report_snapshot'])]
    public array $options = [];

    #[ORM\Column(type: 'json')]
    #[Groups(['report_snapshot'])]
    public array $xTotals = [];

    #[ORM\Column(type: 'json')]
    #[Groups(['report_snapshot'])]
    public array $yTotals = [];

    #[ORM\Column(type: 'float')]
    #[Groups(['report_snapshot'])]
    public float $total = 0.0;

    #[ORM\Column(name: '`rows`', type: 'json')]
    #[Groups(['report_snapshot'])]
    public array $rows = [];

    #[ORM\Column(type: 'json')]
    #[Groups(['report_snapshot'])]
    public array $metadata = [];

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    public function __toString(): string
    {
        return (string) $this->getId();
    }

    public function getId(): int
    {
        return $this->id;
    }
}
