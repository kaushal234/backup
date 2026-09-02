<?php

declare(strict_types=1);

namespace AppBundle\Controller\Chat;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/chat')]
class AskController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Route('/{id}/messages', name: 'chat_send_message', methods: ['POST'])]
    public function __invoke(Request $request, int $id): Response
    {
        $input = $request->request->get('input', '');
        /** @var UploadedFile|null $file */
        $file = $request->files->get('file');

        $logIri = \sprintf('/ai_logs/%s', $id);

        $payload = [
            'input' => $input,
            'log' => $logIri,
        ];

        if ($file instanceof UploadedFile) {
            $payload['file'] = DataPart::fromPath(
                $file->getPathname(),
                $file->getClientOriginalName(),
                $file->getMimeType() ?: 'application/octet-stream'
            );
        }

        $formData = new FormDataPart($payload);

        $this->client->post('/ai/dispatch', [
            'headers' => $formData->getPreparedHeaders()->toArray(),
            'body' => $formData->bodyToIterable(),
        ]);

        return new JsonResponse([
            'logIri' => $logIri,
        ]);
    }
}
