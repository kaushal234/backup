<?php

declare(strict_types=1);

namespace App\AI\Factory\Engineering;

use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\EconomicMetricModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\EconomicsHeaderModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\LifecycleDatesModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\MasterEngineeringActivityProcessModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\NonRecurringCostsModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\PlannedCompletionDatesModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\RecurringCostsModel;
use App\AI\Dto\Engineering\MasterEngineeringActivityProcess\ScoringModel;
use App\AI\Factory\Directory\LocationModelFactory;
use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\ModelFactoryInterface;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\EconomicMetric;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\EconomicsHeader;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\LifecycleDates;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\MasterEngineeringActivityProcess;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\NonRecurringCosts;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\PlannedCompletionDates;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\RecurringCosts;
use LegacyBundle\Entity\Engineering\MasterEngineeringActivityProcess\Scoring;

final readonly class MasterEngineeringActivityProcessModelFactory implements ModelFactoryInterface
{
    public function __construct(
        private UserModelFactory $peopleModelFactory,
        private LocationModelFactory $locationModelFactory,
    ) {
    }

    public function supports(string $class): bool
    {
        return MasterEngineeringActivityProcess::class === $class;
    }

    /**
     * @param MasterEngineeringActivityProcess $entity
     */
    public function create(object $entity): MasterEngineeringActivityProcessModel
    {
        return new MasterEngineeringActivityProcessModel(
            productType: $entity->productType,
            model: $entity->model,
            status: $entity->status,
            isPrivate: 'Y' === $entity->isPrivate,
            type: $entity->type,
            purpose: $entity->purpose,
            shortDescription: $entity->shortDescription,
            description: $entity->description,
            resolution: $entity->resolution,
            rejectionReason: $entity->rejectionReason,
            factory: null !== $entity->factory ? $this->locationModelFactory->create($entity->factory) : null,
            poster: null !== $entity->poster ? $this->peopleModelFactory->create($entity->poster) : null,
            projectLeader: null !== $entity->projectLeader ? $this->peopleModelFactory->create($entity->projectLeader) : null,
            economicsHeader: $this->createEconomicsHeader($entity->economicsHeader),
            nonRecurringCosts: $this->createNonRecurringCosts($entity->nonRecurringCosts),
            recurringCosts: $this->createRecurringCosts($entity->recurringCosts),
            plannedCompletionDates: $this->createPlannedCompletionDates($entity->plannedCompletionDates),
            lifecycleDates: $this->createLifecycleDates($entity->lifecycleDates),
            scoring: $this->createScoring($entity->scoring),
        );
    }

    private function createEconomicsHeader(EconomicsHeader $header): EconomicsHeaderModel
    {
        return new EconomicsHeaderModel(
            isEngineeringProgramCapitalized: 'Y' === $header->isEngineeringProgramCapitalized,
            programCurrency: $header->programCurrency,
            costCalculationMethod: $header->costCalculationMethod,
        );
    }

    private function createNonRecurringCosts(NonRecurringCosts $costs): NonRecurringCostsModel
    {
        return new NonRecurringCostsModel(
            developmentHours: $this->createEconomicMetric($costs->getDevelopmentHours()),
            subcontractedEngineeringAmount: $this->createEconomicMetric($costs->getSubcontractedEngineeringAmount()),
            materialAndOtherCosts: $this->createEconomicMetric($costs->getMaterialAndOtherCosts()),
            prototypeMaterialCosts: $this->createEconomicMetric($costs->getPrototypeMaterialCosts()),
            prototypeLaborHours: $this->createEconomicMetric($costs->getPrototypeLaborHours()),
        );
    }

    private function createRecurringCosts(RecurringCosts $costs): RecurringCostsModel
    {
        return new RecurringCostsModel(
            material: $this->createEconomicMetric($costs->getMaterial()),
            hours: $this->createEconomicMetric($costs->getHours()),
        );
    }

    private function createEconomicMetric(EconomicMetric $metric): EconomicMetricModel
    {
        return new EconomicMetricModel(
            target: $metric->target,
            estimateAtCompletion: $metric->estimateAtCompletion,
            actual: $metric->actual,
            actualReportedAt: $metric->actualReportedAt,
        );
    }

    private function createPlannedCompletionDates(PlannedCompletionDates $dates): PlannedCompletionDatesModel
    {
        return new PlannedCompletionDatesModel(
            milestone0: $dates->milestone0,
            milestone1: $dates->milestone1,
            milestone2: $dates->milestone2,
            milestone3: $dates->milestone3,
            milestone4: $dates->milestone4,
        );
    }

    private function createLifecycleDates(LifecycleDates $dates): LifecycleDatesModel
    {
        return new LifecycleDatesModel(
            createdOn: $dates->createdOn,
            closedOn: $dates->closedOn,
            suspendedOn: $dates->suspendedOn,
            suspendedDaysCount: $dates->suspendedDaysCount,
        );
    }

    private function createScoring(Scoring $scoring): ScoringModel
    {
        return new ScoringModel(
            importanceFactor: $scoring->importanceFactor,
            finalWeight: $scoring->finalWeight,
        );
    }
}
