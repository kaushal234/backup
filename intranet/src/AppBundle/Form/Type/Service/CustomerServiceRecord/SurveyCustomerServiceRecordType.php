<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyCustomerServiceRecordType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('answerSurveyCustomerServiceRecords', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'label' => false,
                'entry_options' => ['label' => 'false'],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
            'label' => false,
        ]);
    }
}
