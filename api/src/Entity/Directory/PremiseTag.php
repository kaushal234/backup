<?php

declare(strict_types=1);

namespace App\Entity\Directory;

use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Entity\AbstractTag;
use App\Filter\SimpleSearchFilter;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(),
        new Post(security: "is_granted('FEATURE_PREMISE_WRITE')"),
        new Get(),
        new Put(security: "is_granted('FEATURE_PREMISE_WRITE')"),
        new Delete(security: "is_granted('FEATURE_PREMISE_WRITE')"),
    ],
    normalizationContext: ['groups' => ['tag']],
    denormalizationContext: ['groups' => ['premise_tag_write', 'tag_write']],
)]
#[ORM\Table(name: 'premises_tags')]
#[ApiFilter(SimpleSearchFilter::class, properties: ['name' => 'partial'])]
#[LockedValue(value: PremiseTag::HOME_OFFICE, propertyPath: 'name')]
#[LockedValue(value: PremiseTag::HOME_OFFICE_RESTRICTED, propertyPath: 'name')]
class PremiseTag extends AbstractTag
{
    /** @var string */
    final public const HOME_OFFICE = 'Home Office';
    /** @var string */
    final public const HOME_OFFICE_RESTRICTED = 'Home Office (restricted)';

    /**
     * @var Collection<Premise>
     */
    #[ORM\ManyToMany(targetEntity: 'App\Entity\Directory\Premise', inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'premises_tags_xref')]
    private Collection $premises;

    public function __construct()
    {
        $this->premises = new ArrayCollection();
    }

    /**
     * @return Collection<Premise>
     */
    public function getPremises(): Collection
    {
        return $this->premises;
    }

    public function addPremise(Premise $premise): self
    {
        $this->premises->add($premise);

        return $this;
    }

    public function removePremise(Premise $premise): self
    {
        $this->premises->removeElement($premise);

        return $this;
    }
}
