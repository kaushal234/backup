<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\IndiceFactor;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Support\UnitOperationalStatus;
use App\Workflow\Handler\ChainWorkflowHandler;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::REQUEST, method: 'onCreate', priority: EventPriorities::POST_DESERIALIZE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreationApplyPendingStatus', priority: EventPriorities::PRE_VALIDATE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreationUpdateEquipmentRecordAirport', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreationUpdateEquipmentRecordSalesOrganisationService', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreationUpdateEquipmentRecordCustomerSerialNumber', priority: EventPriorities::PRE_WRITE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreationApplyIndiceFactorForExtranetUser', priority: EventPriorities::POST_WRITE)]
class TechnicianOnCallCreateListener extends AbstractTechnicianOnCallListener
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
    ) {
    }

    public function onCreate(RequestEvent $event): void
    {
        $technicianOnCall = $event->getRequest()->attributes->get('data');
        if (!$technicianOnCall instanceof TechnicianOnCall) {
            return;
        }

        $request = $event->getRequest();
        if (Request::METHOD_POST !== $request->getMethod()) {
            return;
        }

        if (null === $technicianOnCall->equipmentRecord) {
            return;
        }

        if (null === $technicianOnCall->customer) {
            $technicianOnCall->customer = $technicianOnCall->equipmentRecord->getEndUser();
        }

        if (null === $technicianOnCall->salesOrganisationService) {
            $technicianOnCall->salesOrganisationService = $technicianOnCall->equipmentRecord->getSalesOrganisationService();
        }
    }

    public function onCreationApplyPendingStatus(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        $technicianOnCall = $event->getControllerResult();

        $this->serviceLocator->get(ChainWorkflowHandler::class)->handle($technicianOnCall, null);
    }

    public function onCreationUpdateEquipmentRecordAirport(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $event->getControllerResult();

        if (!$technicianOnCall->equipmentRecord) {
            return;
        }

        if ($technicianOnCall->airport === $technicianOnCall->equipmentRecord->getAirport()) {
            return;
        }

        $technicianOnCall->equipmentRecord->setAirport($technicianOnCall->airport);
    }

    public function onCreationUpdateEquipmentRecordSalesOrganisationService(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        $technicianOnCall = $event->getControllerResult();

        if (!$technicianOnCall->equipmentRecord) {
            return;
        }

        if ($technicianOnCall->salesOrganisationService === $technicianOnCall->equipmentRecord->getSalesOrganisationService()) {
            return;
        }

        $technicianOnCall->equipmentRecord->setSalesOrganisationService($technicianOnCall->salesOrganisationService);
    }

    public function onCreationUpdateEquipmentRecordCustomerSerialNumber(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $event->getControllerResult();

        if (!$technicianOnCall->equipmentRecord || null === $technicianOnCall->serialNumber) {
            return;
        }

        if ($technicianOnCall->serialNumber === $technicianOnCall->equipmentRecord->getCustomerSerialNumber()) {
            return;
        }

        $technicianOnCall->equipmentRecord->setCustomerSerialNumber($technicianOnCall->serialNumber);
    }

    public function onCreationApplyIndiceFactorForExtranetUser(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        /** @var Security $security */
        $security = $this->serviceLocator->get(Security::class);
        $user = $security->getUser();

        if (!$user instanceof ExtranetUser) {
            return;
        }

        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $event->getControllerResult();

        if (ServiceActivity::INFO === $technicianOnCall->serviceActivity->name) {
            $technicianOnCall->indiceFactor = IndiceFactor::IF_1->value;
        }

        if (UnitOperationalStatus::NMC === $technicianOnCall->unitOperationalStatus->getName()) {
            $technicianOnCall->indiceFactor = IndiceFactor::IF_100->value;
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            ChainWorkflowHandler::class,
            Security::class,
        ];
    }
}
