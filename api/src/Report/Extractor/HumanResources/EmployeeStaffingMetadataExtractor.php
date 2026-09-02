<?php

declare(strict_types=1);

namespace App\Report\Extractor\HumanResources;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\ContractType;
use App\Entity\Directory\People;
use App\Entity\Directory\PositionCategory;
use App\Entity\Directory\PositionClassification;
use App\Report\Handler\ReportHandlerInterface;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class EmployeeStaffingMetadataExtractor
{
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;
    private readonly NormalizerInterface $normalizer;

    private ?object $resource = null;
    private array $businessUnits = [];

    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager, NormalizerInterface $normalizer)
    {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
        $this->normalizer = $normalizer;
    }

    public function __invoke()
    {
        $positionClassificationRepository = $this->entityManager->getRepository(PositionClassification::class);
        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $this->entityManager->getRepository(People::class);

        $metadata = [];
        $defaultValues = ['correction' => 0, 'iris' => [], 'comments' => [], 'previousYearCorrectedTotal' => 0, 'budget' => 0, 'reforecast' => 0];
        /** @var PositionClassification[] $classifications */
        $classifications = $positionClassificationRepository->findBy(['businessUnit' => $this->businessUnits]);
        $classificationsMetadata = [];
        foreach ($classifications as $classification) {
            $key = $classification->positionCategory->name;
            $classificationsMetadata[$key] ??= $defaultValues;
            $classificationsMetadata[$key]['correction'] += $classification->correction;
            $classificationsMetadata[$key]['previousYearCorrectedTotal'] += $classification->previousYearCorrectedTotal;
            $classificationsMetadata[$key]['budget'] += $classification->budget;
            $classificationsMetadata[$key]['reforecast'] += $classification->reforecast;
            $classificationsMetadata[$key]['iris'][] = $this->iriConverter->getIriFromResource($classification);
            if ('' !== (string) $classification->comment) {
                $classificationsMetadata[$key]['comments'][] = \sprintf(
                    '(%+d%s) %s',
                    $classification->correction,
                    $this->resource instanceof BusinessUnit ? '' : ' for '.$classification->businessUnit->getName(),
                    $classification->comment
                );
            }
        }

        $metadata['classifications'] = $classificationsMetadata;
        $metadata['uncategorized'] = $peopleRepository->countUncategorizedPeopleByBusinessUnits(...$this->businessUnits);
        $metadata['scope'] = null === $this->resource ? null : (new \ReflectionClass($this->resource))->getShortName();
        $metadata['businessUnits'] = $this->normalizer->normalize($this->businessUnits, 'jsonld', ['groups' => ['business_unit_public']]);

        $xIris = $yIris = [];

        /** @var PositionCategory[] $positionCategories */
        $positionCategories = $this->entityManager->getRepository(PositionCategory::class)->findAll();
        foreach ($positionCategories as $positionCategory) {
            $xIris[$positionCategory->name] = $this->iriConverter->getIriFromResource($positionCategory);
        }

        $contractTypes = $this->entityManager->getRepository(ContractType::class)->findAll();
        foreach ($contractTypes as $contractType) {
            $yIris[$contractType->name] = $this->iriConverter->getIriFromResource($contractType);
        }

        $metadata[ReportHandlerInterface::METADATA_IRIS_X_KEY] = $xIris;
        $metadata[ReportHandlerInterface::METADATA_IRIS_Y_KEY] = $yIris;

        // reset properties to prevent any side effects
        $this->businessUnits = [];
        $this->resource = null;

        return $metadata;
    }

    public function setResource(?object $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function setBusinessUnits(BusinessUnit ...$businessUnits): self
    {
        $this->businessUnits = $businessUnits;

        return $this;
    }
}
