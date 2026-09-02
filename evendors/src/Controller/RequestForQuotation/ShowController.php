<?php

declare(strict_types=1);

namespace App\Controller\RequestForQuotation;

use App\Http\Responder;
use App\Sdk\ClientInterface;
use App\Sdk\Resource\RequestForQuotation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/request-for-quotation')]
final class ShowController
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/show/{id}/{erp}', name: 'request-for-quotation:show', methods: [Request::METHOD_GET])]
    public function __invoke(int|string $id, ?int $erp = null): Response
    {
        if (null === $erp) {
            $request = $this->client->find(RequestForQuotation::class, ['id' => (string) $id]);
        } else {
            $request = $this->client->find(RequestForQuotation::class, ['id' => (string) $id, 'erp' => (string) $erp]);
        }

        return $this->responder->render('request-for-quotation/show.html.twig', [
            'request' => $request,
        ]);
    }
}
