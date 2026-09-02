<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Sales\ExtranetUserProfile;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class ExtranetUserContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;
    private readonly Security $security;

    public function __construct(SerializerContextBuilderInterface $decorated, Security $security)
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

        if (
            !$normalization
            && !isset($context['input'])
            && (ExtranetUser::class === $context['resource_class'] || ExtranetUserProfile::class === $context['resource_class'])
            && $this->security->getUser() instanceof People
        ) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'extranet_user_full_write';
        }

        if ($normalization
            && ExtranetUser::class === $context['resource_class']
            && null === $request->attributes->get('data')
            && \in_array('extranet_user', $context[AbstractObjectNormalizer::GROUPS] ?? [], true)
        ) {
            // We are trying to add this group in the rReadListener only because we don't want it to be serialized in the response
            $context[AbstractObjectNormalizer::GROUPS][] = 'extranet_user_fetch_eager';
        }

        return $context;
    }
}
