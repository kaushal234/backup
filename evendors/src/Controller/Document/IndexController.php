<?php

declare(strict_types=1);

namespace App\Controller\Document;

use App\CQRS\Query\Document\FindAllDocumentsQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/document')]
final class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $bus,
    ) {
    }

    #[Route('/', name: 'document:index', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): Response
    {
        $documents = $this->bus->dispatch(new FindAllDocumentsQuery());

        return $this->responder->render('document/index.html.twig', [
            'documents' => $documents,
        ]);
    }
}
