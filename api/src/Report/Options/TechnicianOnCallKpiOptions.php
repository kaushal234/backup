<?php

declare(strict_types=1);

namespace App\Report\Options;

use App\Util\IriToId;
use Symfony\Component\OptionsResolver\OptionsResolver;

readonly class TechnicianOnCallKpiOptions
{
    public function __construct(
        private IriToId $iriToId,
    ) {
    }

    public function configureOptions(array $options): array
    {
        $iriToId = $this->iriToId;
        $from = (new \DateTime('first day of -12 months'))->setTime(0, 0);
        $to = (new \DateTime('last day of previous month'))->setTime(23, 59, 59);

        $dateNormalizerFactory = static function (\DateTimeInterface $defaultValue) {
            return static function (OptionsResolver $options, $value) use ($defaultValue) {
                if (null === $value || '' === $value) {
                    return $defaultValue;
                }

                if ($value instanceof \DateTime || $value instanceof \DateTimeImmutable) {
                    return $value->setTime(23, 59, 59);
                }

                try {
                    return (new \DateTime($value))->setTime(23, 59, 59);
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException('Invalid date format.');
                }
            };
        };

        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'salesServiceOrganisation' => null,
            'from' => $from,
            'to' => $to,
            'customers' => null,
        ]);

        $resolver->setNormalizer('from', $dateNormalizerFactory($from));
        $resolver->setNormalizer('to', $dateNormalizerFactory($to));
        $resolver->setNormalizer('salesServiceOrganisation', static function ($options, $value) use ($iriToId) {
            return null === $value || '' === $value ? null : $iriToId->getId($value);
        });
        $resolver->setNormalizer('customers', static function ($options, $value) use ($iriToId) {
            return null === $value || '' === $value ? null : array_map(static function ($iri) use ($iriToId) {
                return $iriToId->getId($iri);
            }, $value);
        });

        return $resolver->resolve($options);
    }
}
