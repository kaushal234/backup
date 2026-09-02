<?php

declare(strict_types=1);

namespace App\Dto\Manufacturing;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\DataProcessor\Sales\LeadTimeDataProcessor;
use App\Entity\Manufacturing\LeadTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Post(
            uriTemplate: '/manufacturing/lead_times/batch',
            securityPostDenormalize: "is_granted('FEATURE_LEAD_TIME_WRITE') or is_granted('MOO_SLT')",
            output: false,
            validate: false,
            processor: LeadTimeDataProcessor::class,
        ),
    ],
    denormalizationContext: ['groups' => ['lead_time:write']]
)]
class LeadTimeBatch
{
    #[Groups('lead_time:write')]
    #[Assert\NotNull]
    public bool $fullUpdate;

    #[Groups('lead_time:write')]
    #[Assert\Valid]
    private Collection $leadTimes;

    /**
     * @var Collection<LeadTime>
     */
    private Collection $persistedLeadTimes;

    public function __construct()
    {
        $this->leadTimes = new ArrayCollection();
        $this->persistedLeadTimes = new ArrayCollection();
    }

    /**
     * @return Collection<LeadTime>
     */
    public function getLeadTimes(): Collection
    {
        return $this->leadTimes;
    }

    public function addLeadTime(LeadTime $leadTime): self
    {
        if (!$this->leadTimes->contains($leadTime)) {
            $this->leadTimes->add($leadTime);
        }

        return $this;
    }

    public function removeLeadTime(LeadTime $leadTime): self
    {
        $this->leadTimes->removeElement($leadTime);

        return $this;
    }

    public function getPersistedLeadTimes(): Collection
    {
        return $this->persistedLeadTimes;
    }

    public function addPersistedLeadTime(LeadTime $leadTime): self
    {
        if (!$this->persistedLeadTimes->contains($leadTime)) {
            $this->persistedLeadTimes->add($leadTime);
        }

        return $this;
    }
}
