<?php

declare(strict_types=1);

namespace App\Controller\ManualDocument;

use App\CQRS\Query\ManualDocument\FindManualDocumentQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\ManualDocument;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/manual-documents')]
class ShowController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/{id}', name: 'manual_document:show', methods: [Request::METHOD_GET])]
    public function __invoke(int $id, Request $request): Response
    {
        /** @var ManualDocument $manualDocument */
        $manualDocument = $this->queryBus->dispatch(new FindManualDocumentQuery(id: $id));

        return $this->responder->render('manual_document/show.html.twig',
            [
                'manualDocument' => $manualDocument,
            ]
        );
    }
}
