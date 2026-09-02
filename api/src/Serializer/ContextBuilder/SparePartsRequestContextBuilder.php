<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Parts\SparePartsRequest;
use App\Entity\Parts\TOCSparePartsRequest;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class SparePartsRequestContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    private readonly Security $security;

    public function __construct(SerializerContextBuilderInterface $decorated, Security $security)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if ($normalization || !\in_array($context['resource_class'], [TOCSparePartsRequest::class, SBSparePartsRequest::class, SparePartsRequest::class], true) || isset($context['input'])) {
            return $context;
        }

        if ($this->security->isGranted('FEATURE_SPARE_PARTS_REQUESTS_EDIT_FULL')) {
            $context[AbstractObjectNormalizer::GROUPS] = array_merge($context[AbstractObjectNormalizer::GROUPS] ?? [], ['spare_parts_request:edit:full']);

            return $context;
        }

        return $context;
    }
}
