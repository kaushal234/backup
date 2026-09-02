<?php

declare(strict_types=1);

namespace App\Controller\WarrantyClaim;

use App\CQRS\Query\WarrantyClaim\FindWarrantyClaimQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/warranty_claims')]
class ShowController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route(path: '/{id}', name: 'warranty_claim:show', methods: [Request::METHOD_GET])]
    public function __invoke(int $id): Response
    {
        return $this->responder->render('warranty_claims/show.html.twig', [
            'warranty_claim' => $this->queryBus->dispatch(new FindWarrantyClaimQuery($id)),
        ]);
    }
}
