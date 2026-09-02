<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Dto\Finance\AccountReceivableOverview;
use App\Dto\Finance\ManufacturingMarginSynthesis;
use App\Serializer\Encoder\XlsxEncoder;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class AccountReceivableOverviewNormalizer implements NormalizerInterface
{
    /**
     * @var string
     */
    final public const CUSTOMER = 'Customer';

    /**
     * @var string
     */
    final public const ERP = 'Erp';

    /**
     * @var string
     */
    final public const TOTAL_VALUE = 'Total Value';

    /**
     * @var string
     */
    final public const TOTAL_PAST_DUE = 'Total Past Due';

    /**
     * @var string
     */
    final public const NOT_PAST_DUE = 'Not Past Due';

    /**
     * @var string
     */
    final public const PAST_DUE_30 = 'Past Due 0-30 days';

    /**
     * @var string
     */
    final public const PAST_DUE_60 = 'Past Due 31-60 days';

    /**
     * @var string
     */
    final public const PAST_DUE_90 = 'Past Due 61-90 days';

    /**
     * @var string
     */
    final public const PAST_DUE_180 = 'Past Due 91-180 days';

    /**
     * @var string
     */
    final public const PAST_DUE_MORE_180 = 'Past Due > 180 days';

    /**
     * @var string
     */
    final public const PAST_DUE_PERCENT = 'Past Due %';

    /**
     * @var string
     */
    final public const PAST_DUE_PERCENT_MORE_60 = 'Past Due > 60 days %';

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof AccountReceivableOverview && XlsxEncoder::FORMAT === $format;
    }

    /**
     * @param AccountReceivableOverview $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        return [
            self::CUSTOMER => $object->customerErpReference->getCustomer()->getName(),
            self::ERP => $object->customerErpReference->getSso()->getErp(),
            ManufacturingMarginSynthesis::CURRENCY => $object->currency,
            self::TOTAL_VALUE => $object->totalValue,
            self::TOTAL_PAST_DUE => $object->totalValue - $object->notPastDue,
            self::NOT_PAST_DUE => $object->notPastDue,
            self::PAST_DUE_30 => $object->pastDueOneMonth,
            self::PAST_DUE_60 => $object->pastDueTwoMonths,
            self::PAST_DUE_90 => $object->pastDueThreeMonths,
            self::PAST_DUE_180 => $object->pastDueSixMonths,
            self::PAST_DUE_MORE_180 => $object->pastDueMoreThanSixMonths,
            self::PAST_DUE_PERCENT => $object->pastDuePercentage,
            self::PAST_DUE_PERCENT_MORE_60 => $object->pastDueTwoMonthsPercentage,
        ];
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }
}
