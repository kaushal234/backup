<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\SelectFormType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;
use Symfony\Component\Validator\Constraints\NotBlank;

class InterventionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $intervention = $builder->getData();

        $statusOptions = $options['openingIntervention'] ?
            [
                'To Continue' => 'TO_CONTINUE',
                'Started' => 'STARTED',
                'Solved' => 'SOLVED',
            ]
            : [
                'To Continue' => 'TO_CONTINUE',
                'Solved' => 'SOLVED',
            ];

        $builder
            ->add('status', SelectFormType::class, [
                'label' => 'intervention.fields.status',
                'choices' => $statusOptions,
                'attr' => [
                    'data-action' => 'change->intervention#onStatusChange',
                    'data-intervention-target' => 'statusDropdown',
                ],
            ])
            ->add('hourmeter', IntegerType::class, [
                'required' => false,
                'label' => new TranslatableMessage('intervention.fields.hourmeter', ['%hourMeter%' => $options['hourMeters']], 'customer_service_record'),
            ])
            ->add('endedDate', DatePickerType::class, [
                'required' => 'STARTED' === $intervention['status'],
                'defaultDate' => new \DateTime(),
                'label' => 'csr.fields.end_date',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'intervention.submit',
                'attr' => [
                    'class' => 'btn btn-info text-capitalize',
                ],
            ])
        ;

        if ($options['enableSurvey']) {
            $builder
                ->add('answerSurveyCustomerServiceRecords', CollectionType::class, [
                    'entry_type' => AnswerType::class,
                    'label' => false,
                    'entry_options' => ['label' => 'false'],
                ]);
        }

        if ($options['openingIntervention']) {
            $builder
                ->add('startedDate', DatePickerType::class, [
                    'required' => true,
                    'defaultDate' => new \DateTime(),
                    'label' => 'csr.fields.start_date',
                ]);
        }

        if ($options['hasToc']) {
            $builder
                ->add('solveToc', CheckboxType::class, [
                    'label' => 'intervention.fields.solveToc',
                    'label_attr' => ['class' => 'col-form-label'],
                    'required' => false,
                    'attr' => [
                        'data-action' => 'change->intervention#onTocSolveChange',
                        'data-intervention-target' => 'solveTocCheckbox',
                    ],
                ])
                ->add('symptoms', TextType::class, [
                    'label' => 'intervention.fields.symptoms',
                    'required' => false,
                ])
                ->add('rootCause', TextType::class, [
                    'label' => 'intervention.fields.rootCause',
                    'required' => false,
                ])
                ->add('solution', TextType::class, [
                    'label' => 'intervention.fields.solution',
                    'required' => false,
                ]);

            $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();

                $solveIntervention = ($data['status'] ?? '') === 'SOLVED';
                $startIntervention = ($data['status'] ?? '') === 'STARTED';
                $solveToc = !empty($data['solveToc']);

                if (!$startIntervention) {
                    $notBlank = [new NotBlank(['message' => 'End date is required when status is solved.'])];

                    $form->add('endedDate', DatePickerType::class, [
                        'required' => true,
                        'constraints' => $notBlank,
                    ]);
                }

                if ($solveIntervention && $solveToc) {
                    $notBlank = [new NotBlank(['message' => 'This field is required.'])];

                    foreach (['symptoms', 'rootCause', 'solution'] as $field) {
                        $form->add($field, TextType::class, [
                            'required' => true,
                            'constraints' => $notBlank,
                        ]);
                    }
                }
            });
        }
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['enableSurvey'] = $options['enableSurvey'];
        $view->vars['hasToc'] = $options['hasToc'];
        $view->vars['openingIntervention'] = $options['openingIntervention'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
            'enableSurvey' => false,
            'hourMeters' => 0,
            'openingIntervention' => false,
            'hasToc' => false,
        ]);
    }
}
