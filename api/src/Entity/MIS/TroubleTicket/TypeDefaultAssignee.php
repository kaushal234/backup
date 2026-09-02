<?php

declare(strict_types=1);

namespace App\Entity\MIS\TroubleTicket;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use App\Doctrine\Mapping\Attributes\Loggable;
use App\Entity\Module\Module;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table]
#[UniqueEntity(fields: ['type', 'module'])]
#[ORM\UniqueConstraint(name: 'unique_default_assignee_per_module_per_type', columns: ['type_id', 'module_id'])]
#[ApiResource(
    operations: [
        new Get(),
        new Delete(
            security: 'is_granted("FEATURE_TYPE_DEFAULT_ASSIGNEE_BATCH")',
        ),
    ],
    routePrefix: 'mis',
    normalizationContext: ['groups' => ['type_assignee']],
)]
#[Loggable(owner: 'module', ownerRelation: 'typeDefaultAssignees')]
class TypeDefaultAssignee
{
    final public const LKU_GKU = 'LKU / GKU';
    final public const OPERATIONAL_OWNER = 'Operational Owner';

    #[ORM\Column(type: 'string')]
    #[Assert\Choice(choices: [self::LKU_GKU, self::OPERATIONAL_OWNER])]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Groups(['type_assignee', 'type_assignee:write'])]
    public string $defaultAssignee;

    #[ORM\ManyToOne(targetEntity: Type::class)]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['type_assignee', 'type_assignee:write'])]
    public Type $type;

    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'typeDefaultAssignees')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    public Module $module;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(['type_assignee'])]
    private int $id;

    public function getId(): int
    {
        return $this->id;
    }
}
