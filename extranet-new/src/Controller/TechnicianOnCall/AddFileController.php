<?php

declare(strict_types=1);

namespace App\Controller\TechnicianOnCall;

use App\CQRS\Command\TechnicianOnCall\AddFileTechnicianOnCallCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\TechnicianOnCall\FindTechnicianOnCallQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\File as FileForm;
use App\Form\Type\FileType;
use App\Http\Responder;
use App\Sdk\Resource\TechnicianOnCall;
use App\Security\Voter\TechnicianOnCallVoter;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[Route(path: '/technician-on-calls')]
final class AddFileController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly FormFactoryInterface $formFactory,
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus,
    ) {
    }

    #[IsGranted(TechnicianOnCallVoter::COMMENT)]
    #[Route(path: '/{id}/files/add', name: 'technician_on_call:file:add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request, int $id): Response
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->queryBus->dispatch(new FindTechnicianOnCallQuery($id));
        $form = $this->formFactory
            ->create(FileType::class, $file = new FileForm())
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new AddFileTechnicianOnCallCommand(
                id: $technicianOnCall->id,
                file: $file->file,
                description: $file->description,
            ));

            $this->responder->flash('success', 'extranet.success.add_file');

            return $this->responder->route('technician_on_call:show', ['id' => $id]);
        }

        return $this->responder->render('technician_on_call/add_file.html.twig', [
            'technicianOnCall' => $technicianOnCall,
            'form' => $form->createView(),
        ]);
    }
}
