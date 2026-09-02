<?php

declare(strict_types=1);

namespace App\ION\Serializer\Normalizer\Manufacturing\JobShop;

use App\Entity\Directory\Location;
use App\Entity\Quality\NonConformity;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class NonConformityByItemContextNormalizer implements NormalizerInterface, NormalizerAwareInterface, ServiceSubscriberInterface
{
    use NormalizerAwareTrait;
    public const ITEM_LIST = 'item_list';
    public const NORMALIZATION_GROUP = 'ion:item:non_conformity';

    /**
     * @var string
     */
    private const ALREADY_CALLED = 'NON_CONFORMITY_BY_ITEM_CONTEXT_NORMALIZER_ALREADY_CALLED';

    private readonly ContainerInterface $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, $format = null, array $context = []): bool
    {
        return
            \in_array(self::NORMALIZATION_GROUP, $context[AbstractNormalizer::GROUPS] ?? [], true)
            && !($context[self::ALREADY_CALLED] ?? null)
        ;
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $itemList[] = $object->getPartNumber();
        foreach ($object->getItems() as $item) {
            $itemList[] = $item->getPartNumber();
        }

        $locationRepository = $this->locator->get(EntityManagerInterface::class)->getRepository(Location::class);
        $location = $locationRepository->findOneBy(['erp' => $object->site]);

        if (!$location) {
            throw new NotFoundHttpException(\sprintf('Location %s not found', $object->site));
        }

        $nonConformityRepository = $this->locator->get(EntityManagerInterface::class)->getRepository(NonConformity::class);
        $result = $nonConformityRepository->getNumberNonConformtityForLocationAndPartnumber($location, $itemList);

        $context[self::ITEM_LIST] = $result;

        return $this->normalizer->normalize($object, $format, $context);
    }

    public static function getSubscribedServices(): array
    {
        return [EntityManagerInterface::class];
    }
}
