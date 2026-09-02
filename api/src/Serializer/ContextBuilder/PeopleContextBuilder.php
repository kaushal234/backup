<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class PeopleContextBuilder implements SerializerContextBuilderInterface
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

        if (People::class !== $context['resource_class']) {
            return $context;
        }

        /** @var Operation $operation */
        $operation = $request->attributes->get('_api_operation');
        $route = $request->attributes->get('_route');

        if ($normalization) {
            switch (true) {
                case ($operation instanceof Get || $operation instanceof Put) && 'my_account' !== $operation->getName():
                    /** @var People $people */
                    $people = $request->attributes->get('data');

                    if ($this->security->isGranted('PEOPLE_MENTORING_ACCESS_VOTER', $people)) {
                        $context[AbstractObjectNormalizer::GROUPS][] = 'people:mentoring';
                    }
                    if ($operation instanceof Put) {
                        $context[AbstractObjectNormalizer::GROUPS][] = 'people:alternate_email';
                    }
                    break;
                case $operation instanceof GetCollection && 'people_search' !== $route || $operation instanceof Post:
                    if ($this->security->isGranted('FEATURE_PEOPLE_MENTORING_VIEW')) {
                        $context[AbstractObjectNormalizer::GROUPS][] = 'people:mentoring';
                    }
                    if ($operation instanceof Post) {
                        $context[AbstractObjectNormalizer::GROUPS][] = 'people:alternate_email';
                    }
            }
        }

        $people = null;
        if ($id = $request->attributes->get('id')) {
            $people = $this->provider->provide($this->resourceMetadataCollectionFactory->create(People::class)->getOperation(), ['id' => $id]);
        }
        switch ($request->getMethod()) {
            case Request::METHOD_GET:
                if ('people_search' === $route) {
                    break;
                }

                if ($this->security->isGranted('PEOPLE_CONTRACT_TYPE_VIEW_VOTER', $people)) {
                    $context[AbstractObjectNormalizer::GROUPS][] = 'contract_type';
                }

                if ($operation instanceof Get
                    && ((null !== $id
                        && $people instanceof People
                        && null !== ($user = $this->security->getUser())
                        && $people->getUserIdentifier() === $user->getUserIdentifier()
                    )
                 || $this->security->isGranted('FEATURE_PEOPLE_UPDATE_VOTER', $people))) {
                    $context[AbstractObjectNormalizer::GROUPS][] = 'people:alternate_email';
                }
                break;
            case Request::METHOD_PUT:
                if ($this->security->isGranted('FEATURE_PEOPLE_IDENTITY_UPDATE')) {
                    $context[AbstractObjectNormalizer::GROUPS][] = 'user_identity_write';
                }

                if ($this->security->isGranted('PEOPLE_CONTRACT_TYPE_ADMIN_VOTER', $people)) {
                    if (!$this->security->isGranted('FEATURE_PEOPLE_UPDATE_VOTER', $people) && !$this->security->isGranted('PEOPLE_PARTIAL_UPDATE_VOTER', $people)) {
                        $context[AbstractObjectNormalizer::GROUPS] = [];
                    }

                    $context[AbstractObjectNormalizer::GROUPS][] = 'people_contract:edit';
                    $context[AbstractObjectNormalizer::GROUPS][] = 'contract_type';
                }
        }

        return $context;
    }
}
