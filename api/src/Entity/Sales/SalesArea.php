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
use ApiPlatform\Metadata\Put;
use App\Entity\Country;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Validator\Constraints\Location as ValidLocation;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: 'App\Repository\Sales\SalesAreaRepository')]
#[UniqueEntity(fields: ['asm', 'country', 'sso'], message: 'This ASM is already linked to this country for this SSO')]
#[ApiResource(
    operations: [
        new GetCollection(normalizationContext: ['groups' => ['sales_area', 'people_public', 'country_list', 'network', 'location_public']]),
        new Put(security: "is_granted('FEATURE_COUNTRY_ASM_WRITE')"),
        new Get(),
        new Post(security: "is_granted('FEATURE_COUNTRY_ASM_WRITE')"),
        new Delete(security: "is_granted('FEATURE_COUNTRY_ASM_WRITE')"),
    ],
    normalizationContext: ['groups' => ['sales_area', 'people_public', 'country_detail', 'continent', 'network', 'location_public']],
    denormalizationContext: ['groups' => ['sales_area_write']],
)]
#[ORM\Table(name: 'sales_areas')]
#[ORM\UniqueConstraint(name: 'unique_asm_per_country_and_sso', columns: ['country_id', 'asm_id', 'sso_id'])]
#[ApiFilter(OrderFilter::class, properties: ['country.name' => 'ASC'])]
#[ApiFilter(SearchFilter::class, properties: ['country' => 'exact', 'asm' => 'exact', 'sso' => 'exact'])]
class SalesArea
{
    #[ORM\Column(type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Country', inversedBy: 'salesAreas')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['sales_area', 'sales_area_write'])]
    private Country $country;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\People')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['sales_area', 'sales_area_write', 'sales_area_public'])]
    private People $asm;

    #[ORM\ManyToOne(targetEntity: 'App\Entity\Directory\Location')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
    #[Groups(['sales_area', 'sales_area_write', 'sales_area_public'])]
    #[ValidLocation(sso: true)]
    private Location $sso;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getAsm(): People
    {
        return $this->asm;
    }

    public function setAsm(People $asm): self
    {
        $this->asm = $asm;

        return $this;
    }

    public function getSso(): Location
    {
        return $this->sso;
    }

    public function setSso(Location $sso): self
    {
        $this->sso = $sso;

        return $this;
    }
}
