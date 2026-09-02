<?php

declare(strict_types=1);

namespace App\Entity\Service;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\AbstractTag;
use App\Validator\Constraints\LockedValue;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ApiResource(
    operations: [
        new GetCollection(openapi: true),
        new Get(openapi: true),
    ],
    normalizationContext: ['groups' => ['tag']],
)]
#[ORM\Table(name: 'technician_on_call_tags')]
#[LockedValue(value: TechnicianOnCallTag::IBS, propertyPath: 'name')]
#[LockedValue(value: TechnicianOnCallTag::IHS, propertyPath: 'name')]
#[LockedValue(value: TechnicianOnCallTag::LINK, propertyPath: 'name')]
class TechnicianOnCallTag extends AbstractTag
{
    final public const IBS = 'toc.tags.ibs';
    final public const IHS = 'toc.tags.ihs';
    final public const LINK = 'toc.tags.link';

    /**
     * @var Collection<TechnicianOnCall>
     */
    #[ORM\ManyToMany(targetEntity: TechnicianOnCall::class, inversedBy: 'tags')]
    #[ORM\JoinTable(name: 'technician_on_call_tags_xref')]
    private Collection $technicianOnCalls;

    public function __construct()
    {
        $this->technicianOnCalls = new ArrayCollection();
    }

    /**
     * @return Collection<TechnicianOnCall>
     */
    public function getTechnicianOnCalls(): Collection
    {
        return $this->technicianOnCalls;
    }

    public function addTechnicianOnCall(TechnicianOnCall $technicianOnCall): self
    {
        $this->technicianOnCalls->add($technicianOnCall);

        return $this;
    }

    public function removeTechnicianOnCall(TechnicianOnCall $technicianOnCall): self
    {
        $this->technicianOnCalls->removeElement($technicianOnCall);

        return $this;
    }
}
