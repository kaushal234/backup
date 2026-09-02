<?php

declare(strict_types=1);

namespace App\Controller\TechnicianOnCall;

use App\CQRS\Command\TechnicianOnCall\AddTechnicianOnCallCommentCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\TechnicianOnCall\FindTechnicianOnCallQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\TechnicianOnCall\AddTechnicianOnCallComment;
use App\Form\Type\TechnicianOnCall\CommentType;
use App\Http\Responder;
use App\Sdk\Resource\TechnicianOnCall;
use App\Security\Voter\TechnicianOnCallVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[Route('/technician-on-calls')]
final readonly class CommentController
{
    public function __construct(
        private FormFactoryInterface $factory,
        private CommandBusInterface $bus,
        private Responder $responder,
        private QueryBusInterface $queryBus,
    ) {
    }

    #[IsGranted(TechnicianOnCallVoter::COMMENT)]
    #[Route('/comment/{id}', name: 'technician_on_calls:comment', methods: [Request::METHOD_POST])]
    public function __invoke(Request $request, int $id): Response
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->queryBus->dispatch(new FindTechnicianOnCallQuery($id));

        $dto = new AddTechnicianOnCallComment();
        $form = $this->factory->create(CommentType::class, $dto);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->bus->dispatch(new AddTechnicianOnCallCommentCommand(iri: $technicianOnCall->iri, message: $dto->message, file: $dto->file));

            $this->responder->flash('success', 'purchase_order.comment.add.success');
        }

        foreach ($form->getErrors(true) as $error) {
            $this->responder->flash('danger', $error->getMessage());
        }

        return $this->responder->route('technician_on_call:show', ['id' => $id]);
    }
}
