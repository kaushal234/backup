<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use App\Entity\Sales\SalesForecast;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SalesForecastNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'SALES_FORECAST_NORMALIZER_ALREADY_CALLED';

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SalesForecast && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param SalesForecast $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if (!\in_array('sfr_export', $context[AbstractNormalizer::GROUPS] ?? [], true)) {
            return $normalizedData;
        }

        $normalizedData['asm'] = (string) $object->getAsm();
        $normalizedData['sso']['currency'] = (string) $object->getSso()->getCurrency();

        foreach (['buyer', 'endUser', 'product', 'tier', 'lastCommentedAt', 'airport', 'country'] as $value) {
            if (null === $normalizedData[$value]) {
                unset($normalizedData[$value]);
            }
        }

        $normalizedData['totalSuccessPercentage'] = $object->getCustomerSuccessPercentage() * $object->getSuccessPercentage() / 100;
        $normalizedData['lastComment'] = preg_replace('#\s\s+#', ' ', $object->getLastComment() ?? '');

        return $normalizedData;
    }
}
