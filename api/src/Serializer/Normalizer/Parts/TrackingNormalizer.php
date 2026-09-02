<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Parts;

use App\Entity\Parts\Tracking;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TrackingNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'TRACKING_NORMALIZER_ALREADY_CALLED';
    private readonly RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Tracking && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param Tracking $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);
        $normalizedData['link'] = null;

        if (null !== $object->courier && null !== $object->courier->url) {
            $url = parse_url($object->courier->url);
            $parameters = [];
            parse_str($url['query'] ?? '', $parameters);
            $trackingNumber = $object->trackingNumber;
            if ('FED' === $object->courier->name) {
                $trackingNumber = mb_substr($object->trackingNumber, 22, 12);
                if ('' === $trackingNumber) {
                    $trackingNumber = mb_substr($object->trackingNumber, -15);
                }
            }
            if (null !== $object->courier->parameterName) {
                $parameters[$object->courier->parameterName] = $trackingNumber;
            }
            $url['query'] = http_build_query($parameters);
            $normalizedData['link'] = \sprintf('%s://%s%s?%s',
                $url['scheme'],
                $url['host'],
                $url['path'] ?? '',
                $url['query']
            );
        }

        if (null === $object->courier && null !== $object->getDocument()) {
            $normalizedData['link'] = $this->router->generate('tracking_files', ['id' => $object->getDocument()->getId(), 'trackingId' => $object->getId()], Router::ABSOLUTE_URL);
        }

        return $normalizedData;
    }
}
