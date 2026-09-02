<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use App\Entity\Quality\Derogation;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class DerogationContextBuilder implements SerializerContextBuilderInterface
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

        /** @var Operation|null $operation */
        $operation = $request->attributes->get('_api_operation');
        if (Derogation::class !== $context['resource_class'] || !$request->isMethod(Request::METHOD_PUT) || (null !== $operation && 'update_derogation_status' === $operation->getName())) {
            return $context;
        }

        $user = $this->security->getUser();
        if ($user instanceof People && $this->security->isGranted('FEATURE_DEROGATION_DUE_DATE_ADMIN_VOTER', $request->attributes->get('data'))) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'derogation:due_date';
        }

        if ($user instanceof People && $this->security->isGranted('FEATURE_DEROGATION_PARTIAL_UPDATE_VOTER', $request->attributes->get('data'))) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'derogation:partial_update';
        }

        return $context;
    }
}
