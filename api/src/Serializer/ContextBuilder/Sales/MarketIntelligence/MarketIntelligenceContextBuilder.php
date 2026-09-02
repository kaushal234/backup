<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder\Sales\MarketIntelligence;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class MarketIntelligenceContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);
        if (MarketIntelligence::class !== $context['resource_class'] || Request::METHOD_PUT !== $request->getMethod() || $normalization) {
            return $context;
        }

        $user = $this->security->getUser();
        /** @var MarketIntelligence $marketIntelligence */
        $marketIntelligence = $request->attributes->get('data');
        if ($user === $marketIntelligence->getPoster() || $this->security->isGranted('MOO_MIM')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'market_intelligence:edit_admin';
        }

        return $context;
    }
}
