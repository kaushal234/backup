<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder\MIS;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\MIS\Project\Project;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ProjectContextBuilder implements SerializerContextBuilderInterface
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

        if (Project::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        if (Request::METHOD_PUT !== $request->getMethod()) {
            return $context;
        }

        $context[AbstractObjectNormalizer::GROUPS][] = 'project:edit_manager';
        $context[AbstractObjectNormalizer::GROUPS][] = 'project:edit_owner';

        if ($this->security->isGranted('FEATURE_MIS_PROJECT_CIO_EDIT')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'phase:estimated_date';
            $context[AbstractObjectNormalizer::GROUPS][] = 'phase:revised_date';
        }

        /** @var Project $project */
        $project = $this->provider->provide($this->resourceMetadataCollectionFactory->create(Project::class)->getOperation(), ['id' => $request->attributes->get('id')]);
        $user = $this->security->getUser();
        if ($project->misOwner === $user || $project->projectManager === $user) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'phase:revised_date';
        }

        return $context;
    }
}
