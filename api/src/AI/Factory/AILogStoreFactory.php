<?php

declare(strict_types=1);

namespace App\AI\Factory;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Builder\UserContextBuilder;
use App\AI\Builder\UserMessageBuilder;
use App\AI\Store\AILogStore;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class AILogStoreFactory
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AILogFactory $factory,
        private IriConverterInterface $iriConverter,
        private RequestStack $requestStack,
        private UserMessageBuilder $userMessageBuilder,
        private UserContextBuilder $userContextBuilder,
        private string $legacyUploadDir,
    ) {
    }

    public function createStore(string $logIri): AILogStore
    {
        return new AILogStore(
            entityManager: $this->entityManager,
            factory: $this->factory,
            iriConverter: $this->iriConverter,
            requestStack: $this->requestStack,
            userMessageBuilder: $this->userMessageBuilder,
            userContextBuilder: $this->userContextBuilder,
            legacyUploadDir: $this->legacyUploadDir,
            logIri: $logIri,
        );
    }
}
