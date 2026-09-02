<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer;

use ApiPlatform\Metadata\Exception\ResourceClassNotFoundException;
use ApiPlatform\Metadata\IriConverterInterface;
use App\ApiPlatform\UniqueResourceMetadataCollectionFactory;
use App\Entity\Activity\Activity;
use App\Entity\Activity\Comment;
use App\Entity\Activity\Log;
use App\Entity\AuthorizedApplication;
use App\Entity\Purchasing\VendorUser;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActivityNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /** @var string */
    final public const NORMALIZE_ACTIVITY_ATTRIBUTE = 'normalize_comments';

    /** @var string */
    final public const DISCRIMINATOR_FILTER = 'discriminator';

    /** @var string */
    private const ALREADY_CALLED = 'ACTIVITY_NORMALIZER_ALREADY_CALLED';
    private readonly IriConverterInterface $iriConverter;
    private readonly UniqueResourceMetadataCollectionFactory $resourceMetadataFactory;
    private readonly Security $security;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(IriConverterInterface $iriConverter, UniqueResourceMetadataCollectionFactory $resourceMetadataFactory, Security $security, EntityManagerInterface $entityManager)
    {
        $this->iriConverter = $iriConverter;
        $this->resourceMetadataFactory = $resourceMetadataFactory;
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (!\is_object($data)) {
            return false;
        }

        if (null === ($context['resource_class'] ?? null)) {
            return false;
        }

        return \array_key_exists(self::NORMALIZE_ACTIVITY_ATTRIBUTE, $context) && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    /**
     * @throws ExceptionInterface
     * @throws ResourceClassNotFoundException
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        $apiResource = $this->resourceMetadataFactory->getApiResource($context['resource_class']);

        switch ($context[self::NORMALIZE_ACTIVITY_ATTRIBUTE]) {
            case 'log':
                $class = Log::class;
                break;
            case 'comment':
                $class = Comment::class;
                break;
            default:
                $class = Activity::class;
        }

        $parameters = ['resource' => $this->iriConverter->getIriFromResource($object)];

        if (!empty($filters = $apiResource->getExtraProperties()[self::DISCRIMINATOR_FILTER] ?? [])) {
            $parameters[self::DISCRIMINATOR_FILTER] = $filters;
        }

        if (($user = $this->security->getUser()) instanceof AuthorizedApplication || $user instanceof VendorUser) {
            $parameters['public'] = true;
        }

        $activities = $this->entityManager->getRepository($class)->findBy($parameters, ['createdAt' => Criteria::DESC]);
        $normalizedData['activity'] = $this->normalizer->normalize($activities, 'jsonld', [AbstractNormalizer::GROUPS => ['activity', 'people_public', 'file:light', 'expose_legacy']]);

        return $normalizedData;
    }
}
