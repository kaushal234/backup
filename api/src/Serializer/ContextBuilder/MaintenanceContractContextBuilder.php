<?php

declare(strict_types=1);

namespace App\Serializer\ContextBuilder;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Directory\People;
use App\Entity\Support\MaintenanceContract;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

class MaintenanceContractContextBuilder implements SerializerContextBuilderInterface
{
    private readonly SerializerContextBuilderInterface $decorated;

    private readonly TokenStorageInterface $tokenStorage;

    public function __construct(SerializerContextBuilderInterface $decorated, TokenStorageInterface $tokenStorage)
    {
        $this->decorated = $decorated;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        if (
            MaintenanceContract::class === $context['resource_class']
            && $normalization
            && isset($context[AbstractObjectNormalizer::GROUPS])
            && null !== ($token = $this->tokenStorage->getToken())
            && $token->getUser() instanceof People
        ) {
            $context[AbstractObjectNormalizer::GROUPS][] = 'maintenance_contract_user_list';
        }

        return $context;
    }
}
