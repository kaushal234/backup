<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Service;

use ApiPlatform\Metadata\Get;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Entity\Support\ProductDemeritClaim;
use LegacyBundle\Entity\WarrantyClaim;
use LegacyBundle\Manager\ModLinkManager;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TechnicianOnCallLinksNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'TECHNICIAN_ON_CALL_LINKS_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityManagerInterface $legacyEntityManager,
        private readonly ModLinkManager $modLinkManager,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof TechnicianOnCall && ($context['operation'] ?? null) instanceof Get && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @param TechnicianOnCall $object
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $links = $this->modLinkManager->getFromToLinks($object->getId(), TechnicianOnCall::MODULE_NAME);

        $result = [];

        foreach ($links as $link) {
            $pair = match (true) {
                TechnicianOnCall::MODULE_NAME === $link['type'] => [$link['module'], $link['parent_id']],
                TechnicianOnCall::MODULE_NAME === $link['module'] => [$link['type'], $link['item']],
                default => null,
            };

            if (null === $pair) {
                continue;
            }

            [$key, $value] = $pair;

            $result[$key][] = (string) $value;
        }

        foreach ($result as $module => $values) {
            switch ($module) {
                case 'PDC':
                    $repository = $this->legacyEntityManager->getRepository(ProductDemeritClaim::class);
                    $response = $repository->findBy(['id' => $values]);
                    $normalizedData['links'][$module] = $this->normalizer->normalize($response, 'json');
                    break;
                case 'WC':
                    $repository = $this->legacyEntityManager->getRepository(WarrantyClaim::class);
                    $response = $repository->findBy(['id' => $values]);
                    $normalizedData['links'][$module] = $this->normalizer->normalize($response, 'json');
                    break;
                default:
                    break;
            }
        }

        return $normalizedData;
    }
}
