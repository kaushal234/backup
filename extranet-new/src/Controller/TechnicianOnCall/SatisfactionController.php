<?php

declare(strict_types=1);

namespace App\Controller\TechnicianOnCall;

use App\CQRS\Command\Service\TechnicianOnCallSatisfactionCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\TechnicianOnCall\FindTechnicianOnCallWithTokenQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\TechnicianOnCall\CreateTechnicianOnCallSurvey;
use App\Form\Type\Service\TechnicianOnCallSatisfactionType;
use App\Http\Responder;
use App\Sdk\Resource\TechnicianOnCall;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/technician-on-calls')]
class SatisfactionController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $commandBus,
        private readonly FormFactoryInterface $formFactory,
    ) {
    }

    #[Route(path: '/{id}/satisfaction/{token}', name: 'technician_on_call:satisfaction', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(int $id, string $token, Request $request): Response
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $this->queryBus->dispatch(new FindTechnicianOnCallWithTokenQuery($id, $token));

        if ($token !== $technicianOnCall->token) {
            $this->responder->flash('danger', 'extranet.error.token_invalid');

            return $this->responder->route('technician_on_call:show', ['id' => $technicianOnCall->id]);
        }

        if ($technicianOnCall->survey) {
            $this->responder->flash('warning', 'extranet.error.survey_exist');

            return $this->responder->route('technician_on_call:show', ['id' => $technicianOnCall->id]);
        }

        $form = $this->formFactory->create(TechnicianOnCallSatisfactionType::class, $survey = new CreateTechnicianOnCallSurvey());
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->commandBus->dispatch(new TechnicianOnCallSatisfactionCommand(
                execution: $survey->execution,
                responsiveness: $survey->responsiveness,
                communication: $survey->communication,
                attitude: $survey->attitude,
                comment: $survey->comment,
                technicianOnCall: \sprintf('/service/technician_on_calls/%d', $id),
                token: $token,
            ));

            $this->responder->flash('success', 'extranet.success.survey');

            return $this->responder->route('technician_on_call:show', ['id' => $technicianOnCall->id]);
        }

        return $this->responder->render('technician_on_call/satisfaction.html.twig', [
            'technicianOnCall' => $technicianOnCall,
            'form' => $form->createView(),
        ]);
    }
}
