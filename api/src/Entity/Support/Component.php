<?php

declare(strict_types=1);

namespace App\Entity\Support;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Serializer\Filter\GroupFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[UniqueEntity(fields: ['name'])]
#[ApiResource(
    shortName: 'EquipmentSerialComponent',
    operations: [
        new GetCollection(),
        new Get(),
    ],
    normalizationContext: ['groups' => ['component']],
    security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')"
)]
#[ORM\Table(name: 'equipment_serial_components')]
#[ApiFilter(OrderFilter::class, properties: ['name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['name' => 'exact'])]
#[ApiFilter(GroupFilter::class, arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['signal_code']])]
class Component
{
    /** @var string */
    final public const MANUAL = 'MANUAL';

    /** @var string */
    final public const OBU_LINK = 'OBU, LINK';

    #[ORM\Column(type: 'string', length: 3, nullable: true)]
    #[Groups(['signal_code'])]
    public ?string $signalCode = null;

    #[ORM\Column(type: 'string')]
    #[Groups(['component'])]
    public string $name;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['component'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
