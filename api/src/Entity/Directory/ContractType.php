<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Validator\Constraints\LockedValue;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('CONTRACT_TYPE_ADMIN_VOTER')"),
        new Get(),
        new Put(security: "is_granted('CONTRACT_TYPE_ADMIN_VOTER')"),
        new Delete(security: "is_granted('CONTRACT_TYPE_ADMIN_VOTER')"),
    ],
    normalizationContext: ['groups' => ['contract_type']],
    denormalizationContext: ['groups' => ['contract_type:write']],
)]
#[ORM\Table]
#[ORM\UniqueConstraint(name: 'unique_contract_name', columns: ['name'])]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[Loggable]
#[LockedValue(value: self::TEMP_AND_CONSULTANTS, propertyPath: 'name')]
class ContractType
{
    public const TEMP_AND_CONSULTANTS = 'Temps & consultants';

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Groups(['contract_type', 'contract_type:write', 'people:export:restricted'])]
    public string $name;

    #[ORM\Column(type: 'string')]
    #[Assert\NotNull]
    #[Groups(['contract_type', 'contract_type:write'])]
    public string $description;

    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['contract_type'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
