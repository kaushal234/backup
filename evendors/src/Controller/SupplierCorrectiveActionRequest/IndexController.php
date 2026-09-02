<?php

declare(strict_types=1);

namespace App\Controller\SupplierCorrectiveActionRequest;

use App\CQRS\Query\SupplierCorrectiveActionRequest\FindAllSupplierCorrectiveActionRequestsQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/supplier-corrective-action-request')]
final class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $bus,
    ) {
    }

    #[Route('/', name: 'supplier-corrective-action-request:index', methods: [Request::METHOD_GET])]
    public function __invoke(): Response
    {
        /** @var AccessibleCollectionInterface<int, SupplierCorrectiveActionRequest> $requests */
        $requests = $this->bus->dispatch(new FindAllSupplierCorrectiveActionRequestsQuery());

        return $this->responder->render('supplier-corrective-action-request/index.html.twig', [
            'requests' => $requests,
        ]);
    }
}
