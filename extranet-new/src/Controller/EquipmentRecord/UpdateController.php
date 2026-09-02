<?php

declare(strict_types=1);

namespace App\Controller\EquipmentRecord;

use App\CQRS\Command\Equipment\UpdateEquipmentCommand;
use App\CQRS\CommandBusInterface;
use App\DataTransferObject\UpdateEquipmentRecord;
use App\Form\Type\EquipmentType;
use App\Http\Responder;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/equipments')]
class UpdateController
{
    public function __construct(
        private readonly Responder $responder,
        private readonly FormFactoryInterface $formFactory,
        private readonly CommandBusInterface $commandBus,
    ) {
    }

    #[Route(path: '/{id}/update', name: 'equipment:update', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function __invoke(Request $request, int $id): Response
    {
        $form = $this->formFactory->create(EquipmentType::class, $equipment = new UpdateEquipmentRecord());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->commandBus->dispatch(new UpdateEquipmentCommand($equipment));
            } catch (HandlerFailedException $exception) {
                /** @var ClientException $clientException */
                $clientException = array_values($exception->getWrappedExceptions(ClientException::class))[0];

                $this->responder->flash('danger', $clientException->getMessage());

                return $this->responder->route('equipment:edit', ['id' => $id]);
            }

            $this->responder->flash('success', 'extranet.success.equipment');

            return $this->responder->route('equipment:show', ['id' => $id]);
        }

        foreach ($form->getErrors(true) as $error) {
            $this->responder->flash('danger', $error->getMessage());
        }

        return $this->responder->route('equipment:edit', ['id' => $id]);
    }
}
