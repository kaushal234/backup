<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\File;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class FileContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    private readonly Security $security;

    public function __construct(Security $security, SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
        $this->security = $security;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (File::class !== $context['resource_class']) {
            return $context;
        }

        if ($normalization || (Request::METHOD_POST === $request->getMethod())) {
            return $context;
        }

        if ($this->security->isGranted('DESCRIPTION_FILE_VOTER', $request->attributes->get('data'))) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'file_description_write';
        }

        if ($this->security->isGranted('FEATURE_NON_CONFORMITY_FILE_CHANGE_VISIBILITY')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'file_public_write';
        }

        if ($this->security->isGranted('FEATURE_CRAB_FILE_CHANGE_VISIBILITY')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'file_public_write';
        }

        if ($this->security->isGranted('FEATURE_MIS_PROJECT_CHANGE_VISIBILITY')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'file_public_write';
        }

        return $context;
    }
}
