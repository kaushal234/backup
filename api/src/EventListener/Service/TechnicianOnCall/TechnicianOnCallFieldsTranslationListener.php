<?php

declare(strict_types=1);

namespace App\EventListener\Service\TechnicianOnCall;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Service\TechnicianOnCall;
use App\Manager\EntityPropertiesTranslationManager;
use Psr\Container\ContainerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::VIEW, method: 'onCreationTranslateTitleAndDescription', priority: EventPriorities::PRE_VALIDATE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onEditionTranslateTitleAndDescription', priority: EventPriorities::PRE_VALIDATE)]
#[AsEventListener(event: KernelEvents::VIEW, method: 'onSolvedTranslateClosingFields', priority: EventPriorities::PRE_VALIDATE)]
class TechnicianOnCallFieldsTranslationListener extends AbstractTechnicianOnCallListener
{
    public function __construct(
        private readonly ContainerInterface $serviceLocator,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            EntityPropertiesTranslationManager::class,
        ];
    }

    public function onCreationTranslateTitleAndDescription(ViewEvent $event): void
    {
        if (!$this->isTechnicianOnCallCreation($event)) {
            return;
        }

        $technicianOnCall = $event->getControllerResult();

        $this->serviceLocator->get(EntityPropertiesTranslationManager::class)->translateObjectProperties($technicianOnCall, [
            'originalTitle' => 'title',
            'originalDescription' => 'description',
        ]);
    }

    public function onEditionTranslateTitleAndDescription(ViewEvent $event): void
    {
        if ('technician_on_call_edit' !== $event->getRequest()->attributes->get('_route')) {
            return;
        }

        $propertiesToTranslate = [];

        /** @var TechnicianOnCall $previousToc */
        $previousToc = $event->getRequest()->attributes->get('previous_data');

        /** @var TechnicianOnCall $editedToc */
        $editedToc = $event->getControllerResult();

        if ($previousToc->originalTitle !== $editedToc->originalTitle) {
            $propertiesToTranslate['originalTitle'] = 'title';
        }

        if ($previousToc->originalDescription !== $editedToc->originalDescription) {
            $propertiesToTranslate['originalDescription'] = 'description';
        }

        if (0 === \count($propertiesToTranslate)) {
            return;
        }

        $this->serviceLocator->get(EntityPropertiesTranslationManager::class)->translateObjectProperties($editedToc, $propertiesToTranslate);
    }

    public function onSolvedTranslateClosingFields(ViewEvent $event): void
    {
        /** @var TechnicianOnCall $technicianOnCall */
        $technicianOnCall = $event->getControllerResult();
        if ('technician_on_call_status' !== $event->getRequest()->attributes->get('_route') || TechnicianOnCall::SOLVED !== $technicianOnCall->status) {
            return;
        }

        $this->serviceLocator->get(EntityPropertiesTranslationManager::class)->translateObjectProperties(
            $technicianOnCall,
            [
                'originalSymptoms' => 'symptoms',
                'originalRootCause' => 'rootCause',
                'originalSolution' => 'solution',
            ]
        );
    }
}
