<?php

declare(strict_types=1);

namespace AppBundle\Controller\Chat;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/chat')]
class CreateChatController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route('/conversations', name: 'chat_create_conversation', methods: ['POST'])]
    public function __invoke(Request $request): Response
    {
        $aiLog = $this->client->post('/ai_logs', ['json' => ['type' => 'chat']]);

        return new JsonResponse([
            'logIri' => $aiLog['@id'],
            'id' => $aiLog['id'] ?? null,
        ]);
    }
}
