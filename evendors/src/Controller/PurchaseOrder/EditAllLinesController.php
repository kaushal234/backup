<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder;

use App\CQRS\Command\PurchaseOrder\EditPurchaseOrderAllLineCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\PurchaseOrder\FindOnePurchaseOrderQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\PurchaseOrder\EditAllPurchaseOrderLine;
use App\Form\PurchaseOrder\EditAllLinesType;
use App\Http\Responder;
use App\Sdk\Resource\PurchaseOrder;
use Exception;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/purchase-order')]
final class EditAllLinesController
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $bus,
        private readonly Responder $responder,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/edit_all_lines_delivery_date/{id}/{erp}', name: 'purchase-order:allLines:edit', methods: Request::METHOD_POST)]
    public function __invoke(Request $request, int|string $id, int $erp): Response
    {
        try {
            /** @var PurchaseOrder $order */
            $order = $this->queryBus->dispatch(new FindOnePurchaseOrderQuery($id, $erp));
            $orderToEdit = EditAllPurchaseOrderLine::fromPurchaseOrder($order);
            $builder = $this->factory->createNamedBuilder(EditAllLinesType::createName(), EditAllLinesType::class, $orderToEdit);
            $builder->setAction($this->urlGenerator->generate('purchase-order:allLines:edit', ['id' => $order->id, 'erp' => $order->erp]));

            $linesForm = $builder->getForm();
            $linesForm->handleRequest($request);

            if ($linesForm->isSubmitted() && $linesForm->isValid()) {
                if ($orderToEdit->hasEditedLines()) {
                    $this->bus->dispatch(new EditPurchaseOrderAllLineCommand($linesForm->getData()));
                }

                $this->responder->flash('success', 'purchase_order.success');
            } else {
                $this->responder->flash('danger', 'purchase_order.edit.error');

                return $this->responder->render('purchase-order/show.html.twig', [
                    'order' => $order,
                    'linesForm' => $linesForm->createView(),
                ]);
            }
        } catch (Exception $e) {
            $this->responder->flash('danger', 'purchase_order.error');
        }

        return $this->responder->route('purchase-order:show', ['id' => $id, 'erp' => $erp]);
    }
}
