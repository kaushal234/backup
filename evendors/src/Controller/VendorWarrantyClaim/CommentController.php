<?php

declare(strict_types=1);

namespace App\Controller\VendorWarrantyClaim;

use App\CQRS\Command\VendorWarrantyClaim\AddVendorWarrantyClaimCommentCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\VendorWarrantyClaim\FindNCRVendorWarrantyClaimQuery;
use App\CQRS\Query\VendorWarrantyClaim\FindWCVendorWarrantyClaimQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\VendorWarrantyClaim\AddVendorWarrantyClaimComment;
use App\Form\VendorWarrantyClaim\CommentType;
use App\Http\Responder;
use App\Sdk\Resource\NCRVendorWarrantyClaim;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vendor-warranty-claim')]
final class CommentController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly FormFactoryInterface $factory,
        private readonly CommandBusInterface $commandBus,
        private readonly Responder $responder,
    ) {
    }

    #[Route('/comment/{module}/{id}', name: 'vendor-warranty-claim:comment', requirements: ['module' => 'NCR|WC'], methods: [Request::METHOD_POST])]
    public function __invoke(Request $request, string $module, string $id): Response
    {
        $claim = match ($module) {
            'NCR' => $this->queryBus->dispatch(new FindNCRVendorWarrantyClaimQuery($id)),
            'WC' => $this->queryBus->dispatch(new FindWCVendorWarrantyClaimQuery($id)),
            default => throw new NotFoundHttpException(),
        };

        $dto = new AddVendorWarrantyClaimComment();
        $form = $this->factory->create(CommentType::class, $dto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new AddVendorWarrantyClaimCommentCommand($claim->iri, $dto->message, $dto->file));

            $this->responder->flash('success', 'purchase_order.comment.add.success');
        }

        foreach ($form->getErrors(true) as $error) {
            $this->responder->flash('danger', $error->getMessage());
        }

        if ($claim instanceof NCRVendorWarrantyClaim) {
            return $this->responder->route('vendor-warranty-claim:show-ncr', ['id' => $id]);
        }

        return $this->responder->route('vendor-warranty-claim:show-wc', ['id' => $id]);
    }
}
