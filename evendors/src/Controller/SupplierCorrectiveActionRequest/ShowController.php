<?php

declare(strict_types=1);

namespace App\Controller\SupplierCorrectiveActionRequest;

use App\CQRS\Query\SupplierCorrectiveActionRequest\FindSupplierCorrectiveActionRequestUsingIdQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/supplier-corrective-action-request')]
final class ShowController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('/{id}', name: 'supplier-corrective-action-request:show', methods: [Request::METHOD_GET])]
    public function __invoke(int $id): Response
    {
        /** @var SupplierCorrectiveActionRequest $request */
        $request = $this->queryBus->dispatch(new FindSupplierCorrectiveActionRequestUsingIdQuery($id));

        return $this->responder->render('supplier-corrective-action-request/show.html.twig', [
            'request' => $request,
        ]);
    }
}
