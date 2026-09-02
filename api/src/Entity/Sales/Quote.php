<?php

declare(strict_types=1);

namespace App\Entity\Sales;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\DataProcessor\Sales\Quote\QuoteDataProcessor;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(
            inputFormats: ['multipart' => ['multipart/form-data']],
            openapi: true,
            security: "is_granted('AUTHORIZED_APPLICATION_FEATURE_SALES_QUOTE_WRITE')",
            name: 'create_quote',
            processor: QuoteDataProcessor::class,
        ),
        new Delete(),
    ],
    routePrefix: 'sales',
    normalizationContext: ['groups' => ['quote']],
    denormalizationContext: ['groups' => ['quote:write']],
    order: ['id' => 'DESC'],
)]
#[ApiFilter(OrderFilter::class, properties: ['id', 'quoteNumber'])]
#[ApiFilter(SearchFilter::class, properties: ['quoteNumber' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'id' => 'exact',
    'quoteNumber' => 'partial',
])]
#[Gedmo\SoftDeleteable]
class Quote implements \Stringable
{
    #[ORM\Column(type: 'string')]
    #[Groups(['quote', 'quote:write', 'quote:light'])]
    public string $quoteNumber;

    #[ORM\Column(type: 'text')]
    #[Groups(['quote', 'quote:write'])]
    public string $xml;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['quote', 'quote:light'])]
    private int $id;

    #[ORM\Column(name: 'deleted_at', type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $deletedAt = null;

    public function __toString(): string
    {
        return $this->quoteNumber;
    }

    public function getId(): int
    {
        return $this->id;
    }
}
