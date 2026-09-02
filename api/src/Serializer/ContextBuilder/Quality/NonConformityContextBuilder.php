<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder\Quality;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Quality\NonConformity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class NonConformityContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        if (NonConformity::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        if ($request->isMethod(Request::METHOD_POST)) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'non_conformity:create';

            return $context;
        }

        if ($this->security->isGranted('FEATURE_NON_CONFORMITY_STATUS')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'non_conformity:status';
        }

        /** @var NonConformity $nonConformity */
        $nonConformity = $this->provider->provide($this->resourceMetadataCollectionFactory->create(NonConformity::class)->getOperation(), ['id' => $request->attributes->get('id')]);
        if ($nonConformity->reportedBy === $this->security->getUser() || $this->security->isGranted('FEATURE_NON_CONFORMITY_EDIT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'non_conformity:edit';
        }

        if ($this->security->isGranted('FEATURE_NON_CONFORMITY_PARTIAL_EDIT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'non_conformity:partial_edit';
        }

        return $context;
    }
}
