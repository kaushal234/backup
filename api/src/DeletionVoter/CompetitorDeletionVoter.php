<?php

declare(strict_types=1);

namespace App\DeletionVoter;

use App\DeletionVoter\Reason\RejectedDeletionDetailedReason;
use App\DeletionVoter\Reason\RejectedDeletionReason;
use App\Entity\Sales\Competitor;
use App\Repository\Sales\CompetitorPricingRepository;
use App\Repository\Sales\ForecastClosureRepository;

class CompetitorDeletionVoter implements DeletionVoterInterface
{
    private readonly CompetitorPricingRepository $competitorPricingRepository;
    private readonly ForecastClosureRepository $forecastClosureRepository;

    public function __construct(CompetitorPricingRepository $competitorPricingRepository, ForecastClosureRepository $forecastClosureRepository)
    {
        $this->competitorPricingRepository = $competitorPricingRepository;
        $this->forecastClosureRepository = $forecastClosureRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($entity): bool
    {
        return $entity instanceof Competitor;
    }

    /**
     * {@inheritdoc}
     *
     * @param Competitor $entity
     */
    public function abstainToDeletion($entity): ?RejectedDeletionReason
    {
        $reason = new RejectedDeletionDetailedReason();
        $reason->setType('competitor')->setLabel($entity->getName());

        if ((bool) ($ids = $this->competitorPricingRepository->getIdentifiersForCompetitor($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))
                ->setCountedType('CPR')
            ;
        }

        if ((bool) ($ids = $this->forecastClosureRepository->getIdentifiersForCompetitor($entity))) {
            return $reason
                ->setIdentifiers(array_column($ids, 'id'))

                ->setCountedType('FCR')
            ;
        }

        return null;
    }
}
