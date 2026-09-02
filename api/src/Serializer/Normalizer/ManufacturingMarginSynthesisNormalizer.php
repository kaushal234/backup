<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Dto\Finance\ManufacturingMarginSynthesis;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ManufacturingMarginSynthesisNormalizer implements NormalizerInterface
{
    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof ManufacturingMarginSynthesis;
    }

    /**
     * @param ManufacturingMarginSynthesis $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        return [
            ManufacturingMarginSynthesis::DATE => $object->getExportDate()->format('Y-m'),
            ManufacturingMarginSynthesis::FINANCE_FAMILY => $object->getFinanceFamily(),
            ManufacturingMarginSynthesis::PRODUCT_TYPE => $object->getProductType(),
            ManufacturingMarginSynthesis::QUANTITY => $object->getQuantity(),
            ManufacturingMarginSynthesis::CURRENCY => $object->getCurrency(),
            ManufacturingMarginSynthesis::AVERAGE_TRANSFER_PRICE => $object->getAverageTransferPrice(),
            ManufacturingMarginSynthesis::AVERAGE_FACTORY_DISCOUNT => $object->getAverageFactoryDiscount(),
            ManufacturingMarginSynthesis::AVERAGE_ACTUAL_DIRECT_MARGIN => $object->getAverageActualDirectMargin(),
            ManufacturingMarginSynthesis::AVERAGE_PROJECTED_DIRECT_MARGIN => $object->getAverageProjectedDirectMargin(),
            ManufacturingMarginSynthesis::AVERAGE_MODEL_BASE_HOURS => $object->getAverageModelBaseHours(),
            ManufacturingMarginSynthesis::AVERAGE_INDUSTRIAL_INCORPORATION_PARAMETER => $object->getAverageIndustrialIncorporationParameter(),
            ManufacturingMarginSynthesis::AVERAGE_OPTION_CONFIGURATION_PARAMETER_HOURS => $object->getAverageOptionConfigurationParameterHours(),
            ManufacturingMarginSynthesis::AVERAGE_UNIT_ALLOCATED_HOURS => $object->getAverageUnitAllocatedHours(),
            ManufacturingMarginSynthesis::AVERAGE_ACTUAL_HOURS => $object->getAverageActualHours(),
            ManufacturingMarginSynthesis::AVERAGE_FACTORY_STANDARD_EFFICIENCY => $object->getAverageFactoryStandardEfficiencyActual(),
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}
