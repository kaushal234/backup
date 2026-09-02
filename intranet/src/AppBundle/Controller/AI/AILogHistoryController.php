<?php

declare(strict_types=1);

namespace AppBundle\Controller\AI;

use ApiBundle\Client;
use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
readonly class AILogHistoryController
{
    public function __construct(
        private Client $client,
    ) {
    }

    #[Route('/directory/people/{id}/ai-history', name: 'ai_log_history', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => 'people'])] ApiData $people): JsonResponse
    {
        $data = $this->client->get('ai_logs/history', ['query' => ['people' => $people->getIri()]]);

        return new JsonResponse($data);
    }
}
