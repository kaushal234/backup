<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\TOCManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class CustomerServiceRecordsCommentsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'CUSTOMER_SERVICE_RECORDS_COMMENTS_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly TOCManager $TOCManager,
        private readonly IriConverterInterface $iriConverter,
        private readonly RequestStack $requestStack,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')] private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
    ) {
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!\array_key_exists('resource_class', $context)) {
            return false;
        }

        if (Comment::class !== $context['resource_class']) {
            return false;
        }

        if (!$this->requestStack->getCurrentRequest()) {
            return false;
        }

        if (Request::METHOD_POST === $this->requestStack->getCurrentRequest()->getMethod()) {
            return false;
        }

        if (!$resource = $this->requestStack->getCurrentRequest()->query->get('resource')) {
            return false;
        }

        return str_contains($resource, 'customer_service_records') && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    public function normalize(mixed $object, ?string $format = null, array $context = []): float|int|bool|\ArrayObject|array|string|null
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($object, $format, $context);

        if ([] === $normalizedData) {
            return $normalizedData;
        }

        $normalizedData['hydra:member'] = array_map(static function ($data) {
            $data['discriminator'] = $data['discriminator'] ?? 'CSR';

            return $data;
        }, $normalizedData['hydra:member']);

        if (!$resource = $this->requestStack->getCurrentRequest()->query->get('resource')) {
            return $normalizedData;
        }

        $customerServiceRecord = $this->iriConverter->getResourceFromIri($resource);

        if (!$customerServiceRecord instanceof TechnicianOnCallCustomerServiceRecord) {
            return $normalizedData;
        }

        $subjectOperation = $this->resourceMetadataFactory->create(Comment::class)
            ->getOperation(forceCollection: true)
            ->withPaginationEnabled(false)
            ->withForceEager(false)
        ;

        $technicianOnCallComments = $this->provider->provide($subjectOperation, [], ['filters' => [
            'normalizationGroups' => ['activity_position'],
            'normalization_groups' => ['file:light'],
            'pagination' => false,
            'resource' => $this->iriConverter->getIriFromResource($customerServiceRecord->getTechnicianOnCall()),
        ]]);

        foreach ($technicianOnCallComments as $comment) {
            $comment = $this->normalizer->normalize($comment, $format, $context);
            $normalizedData['hydra:member'][] = [...$comment, 'discriminator' => TechnicianOnCall::MODULE_NAME];
        }

        $sortingComments = $normalizedData['hydra:member'];
        uasort($sortingComments, static function ($a, $b) {
            return strtotime($a['createdAt']) - strtotime($b['createdAt']);
        });

        $normalizedData['hydra:member'] = array_values($sortingComments);
        $normalizedData['hydra:totalItems'] = \count($normalizedData['hydra:member']);

        return $normalizedData;
    }
}
