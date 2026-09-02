<?php

declare(strict_types=1);

namespace AppBundle\Controller\Chat;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

#[AsController]
#[Route(path: '/chat')]
class RateChatController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route('/{id}/rating', name: 'chat_rate_conversation', methods: ['POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        /** @var array{rating?: mixed, comment?: mixed} $payload */
        $payload = $request->toArray();
        $rating = (int) ($payload['rating'] ?? 0);
        $comment = (string) ($payload['comment'] ?? '');

        try {
            $this->client->post('/ai/ratings', [
                'json' => [
                    'rating' => $rating,
                    'comment' => $comment,
                    'log' => \sprintf('/ai_logs/%s', $id),
                ],
            ]);
        } catch (HttpExceptionInterface $exception) {
            // Surface the API status (e.g. 422 when the conversation is already rated).
            return new JsonResponse(
                ['error' => $exception->getMessage()],
                $exception->getResponse()->getStatusCode(),
            );
        }

        return new JsonResponse(null, Response::HTTP_CREATED);
    }
}
