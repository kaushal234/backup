<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder;

use App\CQRS\Query\PurchaseOrder\FindAllPurchaseOrderOpenQuery;
use App\CQRS\QueryBusInterface;
use App\Http\Responder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/purchase-order')]
final class IndexController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $bus,
    ) {
    }

    #[Route('/', name: 'purchase-order:index', methods: [Request::METHOD_GET])]
    public function __invoke(Request $request): Response
    {
        $orders = $this->bus->dispatch(new FindAllPurchaseOrderOpenQuery());

        return $this->responder->render('purchase-order/index.html.twig', [
            'orders' => $orders,
            'state' => $request->query->get('state'),
        ]);
    }
}
