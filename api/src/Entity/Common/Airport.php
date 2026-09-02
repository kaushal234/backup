<?php

declare(strict_types=1);

namespace App\Entity\Common;

use ApiPlatform\Doctrine\Orm\Filter\ExistsFilter;
use ApiPlatform\Doctrine\Orm\Filter\FreeTextQueryFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Doctrine\Orm\Filter\OrFilter;
use ApiPlatform\Doctrine\Orm\Filter\PartialSearchFilter;
use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\QueryParameter;
use ApiPlatform\Serializer\Filter\GroupFilter;
use App\Doctrine\Mapping\Attributes as App;
use App\Entity\IATACode;
use App\Entity\Service\ServiceArea;
use App\Filter\SimpleSearchFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use LegacyBundle\Doctrine\Mapping\Attributes as Legacy;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
            parameters: [
                'autocomplete' => new QueryParameter(
                    filter: new FreeTextQueryFilter(new OrFilter(new PartialSearchFilter())),
                    description: 'To allow filtering by partial code or cityName.',
                    properties: ['code', 'cityName'],
                ),
            ],
        ),
        new Post(security: "is_granted('FEATURE_IATA_CODE_WRITE') or is_granted('MOO_ER')"),
        new Get(
            security: "is_granted('ACCESS_PEOPLE') or is_granted('ACCESS_EXTRANET_USER')",
        ),
        new Put(security: "is_granted('FEATURE_IATA_CODE_WRITE') or is_granted('MOO_ER')"),
    ],
    normalizationContext: ['groups' => ['iata_code_detail', 'expose_legacy']],
    denormalizationContext: ['groups' => ['iata_code_write']],
)]
#[UniqueEntity(fields: ['code', 'cityName'], errorPath: 'code', ignoreNull: true)]
#[ApiFilter(OrderFilter::class, properties: ['code' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['id' => 'exact', 'legacyId' => 'exact', 'code' => 'exact', 'cityCode3' => 'exact', 'cityName' => 'partial', 'country' => 'exact', 'type' => 'exact', 'serviceAreas' => 'exact'])]
#[ApiFilter(SimpleSearchFilter::class, properties: ['code' => 'exact', 'cityName' => 'partial', 'country.name' => 'exact'])]
#[ApiFilter(GroupFilter::class, id: 'normalization_groups_override', arguments: ['parameterName' => 'normalization_groups_override', 'overrideDefaultGroups' => true, 'whitelist' => ['airport_list']])]
#[ApiFilter(GroupFilter::class, id: 'normalization_groups', arguments: ['parameterName' => 'normalization_groups', 'overrideDefaultGroups' => false, 'whitelist' => ['airport_service_areas']])]
#[ApiFilter(ExistsFilter::class, properties: ['serviceAreas', 'country'])]
#[App\Loggable]
#[Legacy\Synchronize(table: 'airport_codes')]
class Airport extends IATACode
{
    #[Groups(['equipment_record:service', 'equipment_record_detail', 'airport_list', 'sfr_export', 'people:export'])]
    protected string $code;

    #[Assert\Type('float')]
    #[Groups(['equipment_record_detail', 'airport_list'])]
    private float $latitude;

    #[Assert\Type('float')]
    #[Groups(['equipment_record_detail', 'airport_list'])]
    private float $longitude;

    /**
     * @var Collection<int, ServiceArea>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Service\ServiceArea', mappedBy: 'airports', fetch: 'EXTRA_LAZY')]
    #[Groups(['airport_service_areas'])]
    private Collection $serviceAreas;

    public function __construct()
    {
        $this->serviceAreas = new ArrayCollection();
    }

    public function hasCoordinate(): bool
    {
        return null !== $this->getLatitude()
            && null !== $this->getLongitude();
    }

    /**
     * @return Collection<int, ServiceArea>
     */
    public function getServiceAreas(): Collection
    {
        return $this->serviceAreas;
    }
}
