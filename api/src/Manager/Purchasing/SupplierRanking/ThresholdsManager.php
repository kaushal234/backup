<?php

declare(strict_types=1);

namespace App\Manager\Purchasing\SupplierRanking;

use App\Entity\Purchasing\SupplierRanking\Classification;
use App\Entity\Purchasing\SupplierRanking\ExpertiseLevel;
use App\Entity\Purchasing\SupplierRanking\Threshold;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ThresholdsManager
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Method to calculate new classification depending on expertise level and notations using thresholds rules.
     * This algorythm is trying to match supplier ranking data to thresholds rules.
     * And we return the classification attached to threshold rule.
     */
    public function findNewClassification(ExpertiseLevel $expertiseLevel, Collection $notations): Classification
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder
            ->select('threshold', 'classification')
            ->from(Threshold::class, 'threshold')
            ->join('threshold.thresholdsCriterias', 'thresholdCriteria')
            ->join('threshold.classification', 'classification')
            ->andWhere('threshold.expertiseLevel = :expertiseLevel')
            ->setParameter('expertiseLevel', $expertiseLevel)
            // A workflow level to NULL is not considering a classification as automatic updatable value.
            ->andWhere('classification.workflowLevel IS NOT NULL')
        ;

        // This part of request is comparing notations to rank limit of thresholds.
        // If rank limit is greater than or equal to notation, that mean it's matching the rule.
        $andX = $queryBuilder->expr()->orX();
        foreach ($notations as $key => $notation) {
            $andX->add($queryBuilder->expr()->orX(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('thresholdCriteria.criteria', ':criteria_'.$key),
                    $queryBuilder->expr()->gte('thresholdCriteria.rankLimit', ':notation_'.$key)
                )
            ));
            $queryBuilder->setParameter(':criteria_'.$key, $notation->criteria);
            $queryBuilder->setParameter(':notation_'.$key, $notation->notation);
        }
        $queryBuilder->andWhere($andX);

        $queryBuilder->groupBy('threshold');

        // We use this having to count number of matching rules.
        // Because a notation can match a threshold but the rule may need more than one matched threshold to be valide.
        $queryBuilder->having('COUNT(thresholdCriteria.threshold) >= threshold.minCriterias');

        // Ordering workflow level and limit to the first row because we only need the worst classifcation.
        // For exemple, a notation of 1 could match all thresholds, so we're using workflow to choose the right classification.
        $queryBuilder->orderBy('classification.workflowLevel', Criteria::ASC);
        $queryBuilder->setMaxResults(1);

        try {
            $threshold = $queryBuilder->getQuery()->getSingleResult();
        } catch (\Exception $e) {
            $error = 'Thresholds of supplier rankings is not configured correctly. Reason: %s';
            throw new HttpException($e->getCode(), \sprintf($error, $e->getMessage()));
        }

        return $threshold->classification;
    }
}
