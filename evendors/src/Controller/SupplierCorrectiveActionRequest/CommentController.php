<?php

declare(strict_types=1);

namespace App\Controller\SupplierCorrectiveActionRequest;

use App\CQRS\Command\SupplierCorrectiveActionRequest\AddSupplierCorrectiveActionRequestCommentCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\SupplierCorrectiveActionRequest\FindSupplierCorrectiveActionRequestUsingIdQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\SupplierCorrectiveActionRequest\AddSupplierCorrectiveActionRequestComment;
use App\Form\SupplierCorrectiveActionRequest\CommentType;
use App\Http\Responder;
use App\Sdk\Resource\SupplierCorrectiveActionRequest;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/supplier-corrective-action-request')]
final class CommentController
{
    public function __construct(
        private readonly FormFactoryInterface $factory,
        private readonly CommandBusInterface $bus,
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[Route('/comment/{id}', name: 'supplier-corrective-action-request:comment', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request, int $id): Response
    {
        /** @var SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest */
        $supplierCorrectiveActionRequest = $this->queryBus->dispatch(new FindSupplierCorrectiveActionRequestUsingIdQuery($id));

        $dto = new AddSupplierCorrectiveActionRequestComment();
        $form = $this->factory->create(CommentType::class, $dto);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->bus->dispatch(new AddSupplierCorrectiveActionRequestCommentCommand($supplierCorrectiveActionRequest->iri, $dto->message, $dto->file));

            $this->responder->flash('success', 'purchase_order.comment.add.success');
        }

        foreach ($form->getErrors(true) as $error) {
            $this->responder->flash('danger', $error->getMessage());
        }

        return $this->responder->route('supplier-corrective-action-request:show', ['id' => $id]);
    }
}
