<?php

declare(strict_types=1);

namespace App\DataProcessor\Quality\FirstArticleQualification;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Quality\FirstArticleQualification\PlanDuplication;
use App\Manager\Quality\FirstArticleQualification\QualificationPlanDuplicatorManager;

final readonly class DuplicatePlanProcessor implements ProcessorInterface
{
    public function __construct(
        private QualificationPlanDuplicatorManager $duplicator,
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function process(
        mixed $data,
        Operation $operation,
        array $uriVariables = [],
        array $context = [],
    ): PlanDuplication {
        \assert($data instanceof PlanDuplication);

        $created = $this->duplicator->duplicate($data->source, $data->targets);

        // Return IRIs instead of the full FAQ objects: keeps the response tiny
        // and avoids triggering the SOAP-backed serializers on partNumbers.
        $data->duplicatedFor = array_map(
            fn ($faq) => $this->iriConverter->getIriFromResource($faq),
            $created,
        );

        return $data;
    }
}
