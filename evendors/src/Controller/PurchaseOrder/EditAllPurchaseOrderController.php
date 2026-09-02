<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder;

use App\CQRS\Command\PurchaseOrder\EditPurchaseOrderAllLineCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\PurchaseOrder\FindAllPurchaseOrderOpenQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\PurchaseOrder\EditAllPurchaseOrder;
use App\Form\PurchaseOrder\EditAllOrdersType;
use App\Http\Responder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use function count;

#[Route('/purchase-order')]
final class EditAllPurchaseOrderController
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $bus,
        private readonly Responder $responder,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/edit_all_delivery_date', name: 'purchase-order:all:edit', methods: [Request::METHOD_POST, Request::METHOD_GET])]
    public function __invoke(Request $request): Response
    {
        $orders = $this->queryBus->dispatch(new FindAllPurchaseOrderOpenQuery());

        $ordersToEdit = EditAllPurchaseOrder::fromPurchaseOrderCollection($orders);
        $builder = $this->factory->createNamedBuilder(EditAllOrdersType::createName(), EditAllOrdersType::class, $ordersToEdit);
        $builder->setAction($this->urlGenerator->generate('purchase-order:all:edit'));
        $ordersForm = $builder->getForm();
        $ordersForm->handleRequest($request);

        if ($ordersForm->isSubmitted() && $ordersForm->isValid()) {
            // IMPROVEMENT : use async of httpClient to do multiple request
            foreach ($ordersToEdit->getEditPurchaseOrders() as $order) {
                $order->message = $ordersToEdit->message;
                if ($order->hasEditedLines()) {
                    $this->bus->dispatch(new EditPurchaseOrderAllLineCommand($order));
                }
            }

            $this->responder->flash('success', 'purchase_order.success');

            return $this->responder->route('purchase-order:all:edit');
        }

        if (count($ordersForm->getErrors(true))) {
            $this->responder->flash('danger', 'purchase_order.edit.error');
        }

        return $this->responder->render('purchase-order/edit_all_lines_confirmable.html.twig', [
            'orders' => $orders,
            'ordersForm' => $ordersForm->createView(),
            'state' => $request->query->get('state'),
        ]);
    }
}
