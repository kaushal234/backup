<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder\MIS;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Module\Module;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ModuleContextBuilder implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly SerializerContextBuilderInterface $decorated,
        private readonly Security $security
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (Module::class !== $context['resource_class'] || $normalization) {
            return $context;
        }

        if ($this->security->isGranted('FEATURE_MODULE_DISABLED_TROUBLE_TICKET_WRITE')) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'module_write';
        }

        return $context;
    }
}
