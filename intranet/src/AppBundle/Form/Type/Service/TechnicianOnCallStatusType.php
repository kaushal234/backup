<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use ApiBundle\Model\ApiData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class TechnicianOnCallStatusType extends AbstractType
{
    private const STATUS_CONFIG = [
        'CLOSED' => ['class' => 'btn-primary'],
        'SOLVED' => ['class' => 'btn-primary'],
        'SUSPENDED' => ['class' => 'btn-warning'],
        'IN_PROGRESS' => ['class' => 'btn-primary'],
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var ApiData $technicianOnCall */
        $technicianOnCall = $options['data'];
        $availableStatuses = $technicianOnCall->toArray()['availableStatus'];

        if (\in_array('SOLVED', $availableStatuses, true)) {
            $builder
                ->add('symptoms', TextareaType::class, [
                    'label' => 'toc.fields.symptoms',
                    'required' => false,
                    'data' => $technicianOnCall['symptoms'] ?? $technicianOnCall['title'],
                ])
                ->add('rootCause', TextareaType::class, [
                    'label' => 'toc.fields.rootCause',
                    'required' => false,
                ])
                ->add('solution', TextareaType::class, [
                    'label' => 'toc.fields.solution',
                    'required' => false,
                ])
            ;
        }

        foreach ($availableStatuses as $status) {
            $builder->add(mb_strtolower($status), SubmitType::class, [
                'label' => 'toc.button.'.mb_strtolower($status),
                'attr' => array_intersect_key(self::STATUS_CONFIG, array_flip($availableStatuses))[$status] ?? [],
            ]);
        }

        // Transform the clicked button on a status value
        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) {
            $data = $event->getData();
            $data['status'] = mb_strtoupper($event->getForm()->getClickedButton()->getName());
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'technician_on_call',
            'constraints' => [
                new Callback(static function (mixed $value, ExecutionContextInterface $context, mixed $payload) {
                    if ('SOLVED' !== $value['status']) {
                        return;
                    }

                    foreach (['symptoms', 'rootCause', 'solution'] as $field) {
                        if (!$value[$field]) {
                            $context->buildViolation('This value is required')->atPath($field)->addViolation();
                        }
                    }
                }),
            ],
        ]);
    }
}
