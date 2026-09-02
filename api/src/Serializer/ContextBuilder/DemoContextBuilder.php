<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Sales\Demo;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class DemoContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
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

        if (Demo::class !== $context['resource_class'] || $normalization || isset($context['input'])) {
            return $context;
        }

        if (Request::METHOD_POST === $request->getMethod()) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'demo_write_admin';

            return $context;
        }

        if (Request::METHOD_PUT !== $request->getMethod()) {
            return $context;
        }

        $demo = $this->provider->provide($this->resourceMetadataCollectionFactory->create(Demo::class)->getOperation(), ['id' => $request->attributes->get('id')]);

        if (
            $this->authorizationChecker->isGranted('DEMO_ADMIN_EDIT_VOTER', $demo)
            || $this->authorizationChecker->isGranted('FEATURE_DEMO_UPDATE_AST')
            || $this->authorizationChecker->isGranted('MOO_DEMO')
        ) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'demo_write_admin';
        }

        return $context;
    }
}
