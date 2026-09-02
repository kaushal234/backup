<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\SerializerContextBuilderInterface;
use ApiPlatform\State\Util\RequestParser;
use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class FirstArticleQualificationContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly DecoderInterface $decoder,
        #[Autowire(service: 'api_platform.doctrine.orm.state.item_provider')]
        private readonly ProviderInterface $provider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (FirstArticleQualification::class !== $context['resource_class']) {
            return $context;
        }

        // Adding eager loading
        if ($normalization && null === $request->attributes->get('data')) {
            $queryString = RequestParser::getQueryString($request);
            $filters = $queryString ? RequestParser::parseRequestParams($queryString) : [];

            if (\in_array('faq_progress', $filters['normalization_groups'] ?? [], true)) {
                // We are trying to add this group in the rReadListener only because we don't want it to be serialized in the response
                $context[AbstractObjectNormalizer::GROUPS][] = 'faq_fetch_eager';

                return $context;
            }
        }

        if ($normalization) {
            return $context;
        }

        // handling express
        $firstArticleQualification = $this->decoder->decode((string) $request->getContent(), JsonEncoder::FORMAT);
        if ($firstArticleQualification['express'] ?? false) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'faq_plan_item_write';
        }

        if (!$request->isMethod(Request::METHOD_PUT)) {
            return $context;
        }

        if ($this->authorizationChecker->isGranted('FEATURE_FAQ_PLAN_APPROVAL')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'faq_plan_approval_write';
        }

        if (\in_array('faq_plan_item_write', $context[AbstractObjectNormalizer::GROUPS] ?? [], true)) {
            return $context;
        }

        $faq = $this->provider->provide($this->resourceMetadataCollectionFactory->create(FirstArticleQualification::class)->getOperation(), ['id' => $request->attributes->get('id')]);

        if (!$faq instanceof FirstArticleQualification) {
            return $context;
        }

        if ($this->authorizationChecker->isGranted('FEATURE_FAQ_PLAN_WRITE', $faq)) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'faq_plan_item_write';
        }

        return $context;
    }
}
