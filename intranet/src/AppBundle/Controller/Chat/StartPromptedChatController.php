<?php

declare(strict_types=1);

namespace AppBundle\Controller\Chat;

use ApiBundle\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/chat')]
class StartPromptedChatController extends AbstractController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route('/conversations/prompt', name: 'chat_prompt_conversation', methods: ['POST'])]
    public function __invoke(Request $request): RedirectResponse
    {
        $prompt = trim((string) $request->request->get('prompt', ''));

        if ('' === $prompt) {
            return $this->redirectToRoute('chat_home');
        }

        $aiLog = $this->client->post('/ai_logs', ['json' => ['type' => 'chat']]);
        $id = $aiLog['id'];

        $formData = new FormDataPart([
            'input' => $prompt,
            'log' => \sprintf('/ai_logs/%s', $id),
        ]);

        $this->client->post('/ai/dispatch', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return $this->redirectToRoute('chat_show', ['id' => $id]);
    }
}
