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
final class IndexController
{
    public function __construct(
        private readonly ClientInterface $client,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/', name: 'request-for-quotation:index', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        $requests = $this->client->findAll(RequestForQuotation::class);

        return $this->responder->render('request-for-quotation/index.html.twig', [
            'requests' => $requests,
        ]);
    }
}
