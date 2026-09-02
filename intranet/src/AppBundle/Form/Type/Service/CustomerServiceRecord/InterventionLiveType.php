<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use AppBundle\Factory\Service\SurveyCommissioningFactory;
use AppBundle\Form\EventListener\Service\CustomerServiceRecord\InterventionLive\EndedDateListener;
use AppBundle\Form\EventListener\Service\CustomerServiceRecord\InterventionLive\SurveyCommissioningListener;
use AppBundle\Form\EventListener\Service\CustomerServiceRecord\InterventionLive\TocListener;
use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class InterventionLiveType extends AbstractType
{
    public function __construct(
        private readonly SurveyCommissioningFactory $surveyCommissioningFactory,
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $statusList = [
            'To Continue' => 'TO_CONTINUE',
            'Solved' => 'SOLVED',
        ];

        if (($options['customerServiceRecord']['openIntervention']['status'] ?? null) === 'PENDING') {
            $statusList = ['Started' => 'STARTED', ...$statusList];
        }

        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'intervention.fields.status',
                'choices' => $statusList,
                'attr' => [
                    'data-model' => 'intervention_live[status]',
                    'data-action' => 'change->live#update',
                ],
            ])
            ->add('hourmeter', IntegerType::class, [
                'required' => false,
                'label' => new TranslatableMessage('intervention.fields.hourmeter', ['%hourMeter%' => $options['customerServiceRecord']['equipmentRecord']['hourMeter'] ?? 0], 'customer_service_record'),
            ])
        ;

        if (($options['customerServiceRecord']['openIntervention']['status'] ?? null) === 'PENDING') {
            $builder
                ->add('startedDate', DatePickerType::class, [
                    'required' => true,
                    'defaultDate' => new \DateTime(),
                    'label' => 'csr.fields.start_date',
                ]);
        }

        $builder->addEventSubscriber(new EndedDateListener());
        $builder->addEventSubscriber(new SurveyCommissioningListener($this->surveyCommissioningFactory, $options['customerServiceRecord']));
        $builder->addEventSubscriber(new TocListener($options['customerServiceRecord']));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
        ]);

        $resolver->setRequired(['customerServiceRecord']);
        $resolver->setAllowedTypes('customerServiceRecord', 'array');
    }
}
