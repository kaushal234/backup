<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use App\Entity\Quality\Crab;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class CrabContextBuilder implements SerializerContextBuilderInterface
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
        if (Crab::class !== $context['resource_class'] || Request::METHOD_PUT !== $request->getMethod() || $normalization) {
            return $context;
        }

        $user = $this->security->getUser();
        if ($user instanceof People && $this->security->isGranted('FEATURE_CRAB_ADMIN_PART_VOTER', $request->attributes->get('data'))) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'part:admin';
        }

        if ($user instanceof People && $this->security->isGranted('FEATURE_LINK_DEROGATION')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'derogation:link';
        }

        if ($user instanceof People && $this->security->isGranted('FEATURE_CRAB_EDIT_VOTER', $request->attributes->get('data'))) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'crab:update_full';
        }

        if ($user instanceof People) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'crab:update_partial';
        }

        return $context;
    }
}
