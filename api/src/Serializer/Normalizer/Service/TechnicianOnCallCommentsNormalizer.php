<?php

declare(strict_types=1);

namespace App\Serializer\Normalizer\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Filter\ExtraCommentFilter;
use LegacyBundle\Manager\ModLogManager;
use LegacyBundle\Manager\ProductDemeritClaimManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TechnicianOnCallCommentsNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const ALREADY_CALLED = 'TECHNICIAN_ON_CALL_COMMENTS_NORMALIZER_ALREADY_CALLED';

    private const LEGACY_KEYS_MAPPING = [
        'id' => 'id',
        'comment' => 'message',
        'module' => 'discriminator',
        'date' => 'createdAt',
        'firstname' => 'firstname',
        'lastname' => 'lastname',
    ];

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly IriConverterInterface $iriConverter,
        #[Autowire(service: 'api_platform.doctrine.orm.state.collection_provider')] private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly ModLogManager $modLogManager,
        private readonly ProductDemeritClaimManager $productDemeritClaimManager,
        private readonly Security $security,
    ) {
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;
        /** @var array $normalizedData */
        $normalizedData = $this->normalizer->normalize($data, $format, $context);

        $user = $this->security->getUser();

        if (!$user instanceof People) {
            return $normalizedData;
        }

        $normalizedData['hydra:member'] = array_map(static function ($data) {
            $data['discriminator'] = $data['discriminator'] ?? TechnicianOnCall::MODULE_NAME;

            return $data;
        }, $normalizedData['hydra:member']);

        // CSR Comments
        $resource = $this->requestStack->getCurrentRequest()->query->get('resource');
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->iriConverter->getResourceFromIri($resource);
        $iris = $technicianOnCall->customerServiceRecords->map(fn ($customerServiceRecord) => $this->iriConverter->getIriFromResource($customerServiceRecord))->toArray();

        if ([] !== $iris) {
            $subjectOperation = $this->resourceMetadataFactory->create(Comment::class)
                ->getOperation(forceCollection: true)
                ->withPaginationEnabled(false)
                ->withForceEager(false)
            ;
            $customerServiceRecordComments = $this->provider->provide($subjectOperation, [], ['filters' => [
                'normalizationGroups' => ['activity_position'],
                'normalization_groups' => ['file:light'],
                'pagination' => false,
                'resource' => $iris,
            ]]);

            foreach ($customerServiceRecordComments as $comment) {
                $comment = $this->normalizer->normalize($comment, $format, $context);
                $normalizedData['hydra:member'][] = [...$comment, 'discriminator' => 'CSR'];
            }
        }

        // WC Comments
        if (null !== $technicianOnCall->warrantyLegacyId) {
            $legacyWarrantyComments = $this->modLogManager->getModLogs($technicianOnCall->warrantyLegacyId, 'WC');
            $legacyWarrantyComments = $this->legacyCommentFormatter($legacyWarrantyComments);
            $normalizedData['hydra:member'] = [...$normalizedData['hydra:member'], ...$legacyWarrantyComments];
        }

        // PDC comments
        $productDemeritClaimsComments = $this->productDemeritClaimManager->getLogsForLinkTechnicianOnCall($technicianOnCall->getId());
        $productDemeritClaimsComments = $this->legacyCommentFormatter($productDemeritClaimsComments);
        $normalizedData['hydra:member'] = [...$normalizedData['hydra:member'], ...$productDemeritClaimsComments];

        $sortingComments = $normalizedData['hydra:member'];
        uasort($sortingComments, static function ($a, $b) {
            return strtotime($a['createdAt']) - strtotime($b['createdAt']);
        });

        $normalizedData['hydra:member'] = array_values($sortingComments);
        $normalizedData['hydra:totalItems'] = \count($normalizedData['hydra:member']);

        return $normalizedData;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (!\array_key_exists('resource_class', $context)) {
            return false;
        }

        if (Comment::class !== $context['resource_class']) {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return false;
        }

        if (Request::METHOD_POST === $request->getMethod()) {
            return false;
        }

        if (!$resource = $request->query->get('resource')) {
            return false;
        }

        if (!filter_var($request->query->get(ExtraCommentFilter::FILTER_NAME, false), \FILTER_VALIDATE_BOOLEAN)) {
            return false;
        }

        return str_contains($resource, 'technician_on_calls') && null === ($context[self::ALREADY_CALLED] ?? null);
    }

    public function getSupportedTypes(?string $format): array
    {
        return ['*' => false];
    }

    private function legacyCommentFormatter(array $comments = []): array
    {
        $formattedComments = [];
        foreach ($comments as $comment) {
            $formattedComment = ['@type' => 'Comment'];

            foreach ($comment as $legacyKey => $value) {
                if (!\array_key_exists($legacyKey, self::LEGACY_KEYS_MAPPING)) {
                    continue;
                }
                if ('firstname' === $legacyKey || 'lastname' === $legacyKey) {
                    $formattedComment['user'][$legacyKey] = $value;
                } else {
                    $key = self::LEGACY_KEYS_MAPPING[$legacyKey];
                    $formattedComment[$key] = $value;
                }
            }
            $formattedComments[] = $formattedComment;
        }

        return $formattedComments;
    }
}
