<?php

declare(strict_types=1);

namespace App\Controller\PurchaseOrder;

use App\CQRS\Query\PurchaseOrder\FindOnePurchaseOrderQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\PurchaseOrder\EditAllPurchaseOrderLine;
use App\Form\PurchaseOrder\EditAllLinesType;
use App\Http\Responder;
use App\Sdk\Resource\PurchaseOrder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/purchase-order')]
final class ShowController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly FormFactoryInterface $factory,
        private readonly QueryBusInterface $queryBus,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    #[Route('/show/{id}/{erp}', name: 'purchase-order:show', methods: [Request::METHOD_GET])]
    public function __invoke(int|string $id, ?int $erp = null): Response
    {
        /** @var PurchaseOrder $order */
        $order = $this->queryBus->dispatch(new FindOnePurchaseOrderQuery($id, $erp));

        $builder = $this->factory->createNamedBuilder(EditAllLinesType::createName(), EditAllLinesType::class, EditAllPurchaseOrderLine::fromPurchaseOrder($order));
        $builder->setAction($this->urlGenerator->generate('purchase-order:allLines:edit', ['id' => $order->id, 'erp' => $order->erp]));
        $linesForm = $builder->getForm();

        return $this->responder->render('purchase-order/show.html.twig', [
            'order' => $order,
            'linesForm' => $linesForm->createView(),
        ]);
    }
}
