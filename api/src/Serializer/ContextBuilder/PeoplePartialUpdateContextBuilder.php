<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class PeoplePartialUpdateContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if ($normalization || People::class !== $context['resource_class'] || !$request->isMethod(Request::METHOD_PUT)) {
            return $context;
        }

        $subject = $this->provider->provide($this->resourceMetadataCollectionFactory->create(People::class)->getOperation(), ['id' => $request->attributes->get('id')]);

        if ($this->security->isGranted('PEOPLE_PARTIAL_UPDATE_VOTER', $subject) && !$this->security->isGranted('FEATURE_PEOPLE_UPDATE', $subject)) {
            $context['groups'] = array_diff($context['groups'], ['user_write', 'people_write', 'address_write']);
            $context['groups'][] = 'user_partial_write';
        }

        return $context;
    }
}
