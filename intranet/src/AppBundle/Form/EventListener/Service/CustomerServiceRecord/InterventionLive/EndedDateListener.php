<?php

declare(strict_types=1);

namespace AppBundle\Form\EventListener\Service\CustomerServiceRecord\InterventionLive;

use AppBundle\Enum\Service\CustomerServiceRecord\InterventionStatus;
use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class EndedDateListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();
        $status = $data['status'] ?? null;

        if (InterventionStatus::STARTED->value === $status) {
            $event->getForm()->add('endedDate', DatePickerType::class, [
                'required' => true,
                'label' => 'csr.fields.end_date',
            ]);
        }
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $status = $data['status'] ?? null;

        if (\in_array($status, [InterventionStatus::TO_CONTINUE->value, InterventionStatus::SOLVED->value], true)) {
            $event->getForm()->add('endedDate', DatePickerType::class, [
                'required' => true,
                'label' => 'csr.fields.end_date',
            ]);
        }
    }
}
