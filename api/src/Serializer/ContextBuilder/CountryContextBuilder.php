<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Country;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class CountryContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(SerializerContextBuilderInterface $decorated, AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->decorated = $decorated;
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (Country::class !== $context['resource_class']) {
            return $context;
        }

        if ($normalization) {
            return $context;
        }

        if ($this->authorizationChecker->isGranted('FEATURE_COUNTRY_WRITE')) {
            $context[AbstractObjectNormalizer::GROUPS] = ['country_write'];

            return $context;
        }

        if ($this->authorizationChecker->isGranted('FEATURE_COUNTRY_ASM_WRITE')) {
            $context[AbstractObjectNormalizer::GROUPS] = ['country_asm_write'];

            return $context;
        }

        return $context;
    }
}
