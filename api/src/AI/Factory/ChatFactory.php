<?php

declare(strict_types=1);

namespace App\AI\Factory;

use Symfony\AI\Agent\AgentInterface;
use Symfony\AI\Chat\Chat;
use Symfony\AI\Chat\ChatInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final readonly class ChatFactory implements ChatFactoryInterface
{
    public function __construct(
        #[Autowire(service: 'ai.agent.default')]
        private AgentInterface $agent,
        private AILogStoreFactory $storeFactory,
    ) {
    }

    public function createChat(string $iri): ChatInterface
    {
        $store = $this->storeFactory->createStore($iri);

        return new Chat($this->agent, $store);
    }
}
