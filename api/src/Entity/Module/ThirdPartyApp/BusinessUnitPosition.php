<?php

declare(strict_types=1);

namespace App\Entity\Module\ThirdPartyApp;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Link;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Position;
use App\Entity\Module\ThirdPartyApp\Type\Extended;
use App\Filter\ColumnsFilter;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\MaxDepth;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            paginationItemsPerPage: 1000,
        ),
        new Post(
            security: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)"
        ),
        new Get(),
        new Put(
            security: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)"
        ),
        new GetCollection(
            uriTemplate: '/{thirdPartyAppId}/business_unit_positions',
            formats: ['jsonld', 'json', 'csv', 'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']],
            uriVariables: [
                'thirdPartyAppId' => new Link(
                    toProperty: 'thirdPartyApp',
                    fromClass: Extended::class,
                ),
            ],
        ),
        new Delete(
            security: "is_granted('FEATURE_MODULE_WRITE') or is_granted('FEATURE_THIRD_PARTY_APP_ADMIN_VOTER', object)"
        ),
    ],
    routePrefix: '/modules/third_party_app',
    normalizationContext: ['groups' => ['module', 'business_unit_position', 'business_unit', 'position']],
    order: ['position.description' => 'ASC', 'businessUnit.name' => 'ASC'],
)]
#[UniqueEntity(fields: ['thirdPartyApp', 'position', 'businessUnit'])]
#[ORM\Table(name: 'third_party_app_business_unit_position')]
#[ApiFilter(SearchFilter::class, properties: ['thirdPartyApp'])]
#[ApiFilter(SimpleSearchFilter::class, properties: [
    'position.description' => 'partial',
    'businessUnit.name' => 'partial',
])]
#[ApiFilter(OrderFilter::class, properties: ['position.description', 'businessUnit.name'])]
#[ApiFilter(ColumnsFilter::class)]
class BusinessUnitPosition
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    #[Groups(['business_unit_position'])]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Extended::class, inversedBy: 'businessUnitPositions')]
    #[Groups(['business_unit_position'])]
    #[MaxDepth(1)]
    private Extended $thirdPartyApp;

    #[ORM\ManyToOne(targetEntity: Position::class)]
    #[Groups(['business_unit_position'])]
    private Position $position;

    #[ORM\ManyToOne(targetEntity: BusinessUnit::class)]
    #[Groups(['business_unit_position'])]
    private BusinessUnit $businessUnit;

    public function getId(): int
    {
        return $this->id;
    }

    public function getThirdPartyApp(): Extended
    {
        return $this->thirdPartyApp;
    }

    public function setThirdPartyApp(Extended $thirdPartyApp): self
    {
        $this->thirdPartyApp = $thirdPartyApp;

        return $this;
    }

    public function getPosition(): Position
    {
        return $this->position;
    }

    public function setPosition(Position $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getBusinessUnit(): BusinessUnit
    {
        return $this->businessUnit;
    }

    public function setBusinessUnit(BusinessUnit $businessUnit): self
    {
        $this->businessUnit = $businessUnit;

        return $this;
    }
}
