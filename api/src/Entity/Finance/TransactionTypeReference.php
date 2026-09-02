<?php

declare(strict_types=1);

namespace App\Entity\Finance;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Directory\Location;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
    ],
    routePrefix: 'finance',
    normalizationContext: ['groups' => ['transaction_type_reference', 'location_public', 'transaction_type']]
)]
#[ORM\Table(name: 'transaction_type_references')]
#[ORM\UniqueConstraint(name: 'unique_erp_type_per_location', columns: ['erp_type', 'location_id'])]
#[ApiFilter(SearchFilter::class, properties: ['location', 'erpType'])]
class TransactionTypeReference
{
    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction_type_reference'])]
    public Location $location;

    #[ORM\Column(type: 'string')]
    #[Groups(['transaction_type_reference'])]
    public string $erpType;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Finance\TransactionType', fetch: 'EAGER', inversedBy: 'transactionTypeReferences')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['transaction_type_reference'])]
    public TransactionType $transactionType;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
