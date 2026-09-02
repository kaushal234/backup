<?php

declare(strict_types=1);

namespace App\Twig\LiveComponents;

use App\CQRS\Command\TechnicianOnCall\CreateTechnicianOnCallCommand;
use App\CQRS\CommandBusInterface;
use App\CQRS\Query\User\FindUserQuery;
use App\CQRS\QueryBusInterface;
use App\DataTransferObject\TechnicianOnCall\CreateTechnicianOnCall;
use App\Factory\CreateTechnicianOnCallFactory;
use App\Form\Type\TechnicianOnCall\TechnicianOnCallType;
use App\Http\Responder;
use App\Sdk\Client;
use App\Sdk\Resource\EquipmentRecord;
use App\Sdk\Resource\TechnicianOnCallType as TechnicianOnCallTypeResource;
use App\Sdk\Resource\User as ExtranetUser;
use App\Security\User\User;
use Psl\Collection\AccessibleCollectionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class TechnicianOnCallLiveComponent extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?CreateTechnicianOnCall $initialValues = null;

    #[LiveProp(writable: true, onUpdated: 'onEquipmentChange')]
    public ?string $equipmentId = null;

    public function __construct(
        private readonly Client $client,
        private readonly CreateTechnicianOnCallFactory $createTechnicianOnCallFactory,
        private readonly QueryBusInterface $queryBus,
        private readonly Responder $responder,
        private readonly CommandBusInterface $commandBus,
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function mount(?string $equipmentId = null): void
    {
        $this->equipmentId = $equipmentId;

        if (null === $equipmentId) {
            return;
        }

        $equipmentRecord = $this->client->find(EquipmentRecord::class, ['resource_id' => $equipmentId]);
        $this->initialValues = $this->createTechnicianOnCallFactory->createFromEquipmentRecord($equipmentRecord);
    }

    public function onEquipmentChange(): void
    {
        if (!$this->equipmentId) {
            return;
        }

        $equipmentRecord = $this->client->find(EquipmentRecord::class, ['resource_id' => $this->equipmentId]);

        $this->formValues['equipment'] = $equipmentRecord->id;
        $this->formValues['airport'] = $equipmentRecord->airport?->id;
    }

    #[LiveAction]
    public function save(#[CurrentUser] User $currentUser): ?RedirectResponse
    {
        $this->submitForm();

        /** @var CreateTechnicianOnCall $data */
        $data = $this->getForm()->getData();

        try {
            /** @var ExtranetUser $user */
            $user = $this->queryBus->dispatch(new FindUserQuery($currentUser->id));

            /** @var AccessibleCollectionInterface<TechnicianOnCallTypeResource> $technicianOnCallTypes */
            $technicianOnCallTypes = $this->client->findAll(TechnicianOnCallTypeResource::class, [
                'query' => [
                    'name' => 'toc.type.not_define_yet',
                ],
            ]);

            /** @var Envelope $envelope */
            $envelope = $this->commandBus->dispatch(new CreateTechnicianOnCallCommand(
                originalTitle: $data->originalTitle,
                originalDescription: $data->originalDescription,
                serviceActivity: $data->serviceActivity,
                unitOperationalStatus: $data->unitOperationalStatus,
                mainContact: $user->iri,
                hourMeter: $data->hourMeter,
                airport: $data->airport->iri,
                equipmentRecord: $data->equipment->iri,
                errorCodes: $data->errorCodes,
                technicianOnCallType: $technicianOnCallTypes->first()?->iri,
            ));

            $handleStamp = $envelope->last(HandledStamp::class);
            $technicianOnCall = $handleStamp->getResult();

            $this->responder->flash(
                'success',
                $this->translator->trans('extranet.success.technician_on_call', ['%id%' => $technicianOnCall['id']])
            );

            return $this->responder->route('technician_on_call:index');
        } catch (HandlerFailedException $exception) {
            $this->form->addError(new FormError($exception->getPrevious()?->getMessage()));
            $this->formView = null;
            throw new UnprocessableEntityHttpException('Form validation failed in API.');
        }
    }

    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(TechnicianOnCallType::class, $this->initialValues ?? new CreateTechnicianOnCall(), [
            // Disable CSRF protection because we are using live component CSRF protection.
            'csrf_protection' => false,
        ]);
    }
}
