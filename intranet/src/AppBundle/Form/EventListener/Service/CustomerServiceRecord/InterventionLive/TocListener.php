<?php

declare(strict_types=1);

namespace AppBundle\Form\EventListener\Service\CustomerServiceRecord\InterventionLive;

use AppBundle\Enum\Service\CustomerServiceRecord\InterventionStatus;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class TocListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly array $customerServiceRecord,
    ) {
    }

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
        $form = $event->getForm();

        if (isset($data['solveToc'])) {
            $form->add('solveToc', CheckboxType::class, [
                'label' => 'intervention.fields.solveToc',
                'label_attr' => ['class' => 'col-form-label'],
                'required' => false,
                'attr' => [
                    'data-model' => 'intervention_live[solveToc]',
                    'data-action' => 'change->live#update',
                ],
            ]);
        }

        if (isset($data['solveToc']) && $data['solveToc']) {
            $form
                ->add('symptoms', TextType::class, [
                    'label' => 'intervention.fields.symptoms',
                    'required' => true,
                ])
                ->add('rootCause', TextType::class, [
                    'label' => 'intervention.fields.rootCause',
                    'required' => true,
                ])
                ->add('solution', TextType::class, [
                    'label' => 'intervention.fields.solution',
                    'required' => true,
                ])
            ;
        }
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();
        $form = $event->getForm();

        if (InterventionStatus::SOLVED->value === $data['status'] && 'toc' === $this->customerServiceRecord['type']) {
            $form->add('solveToc', CheckboxType::class, [
                'label' => 'intervention.fields.solveToc',
                'label_attr' => ['class' => 'col-form-label'],
                'required' => false,
                'attr' => [
                    'data-model' => 'intervention_live[solveToc]',
                    'data-action' => 'change->live#update',
                ],
            ]);

            if (isset($data['solveToc']) && $data['solveToc']) {
                $form
                    ->add('symptoms', TextType::class, [
                        'label' => 'intervention.fields.symptoms',
                        'required' => true,
                    ])
                    ->add('rootCause', TextType::class, [
                        'label' => 'intervention.fields.rootCause',
                        'required' => true,
                    ])
                    ->add('solution', TextType::class, [
                        'label' => 'intervention.fields.solution',
                        'required' => true,
                    ])
                ;
            }
        }
    }
}
