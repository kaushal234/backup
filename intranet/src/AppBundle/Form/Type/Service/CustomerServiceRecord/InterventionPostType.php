<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InterventionPostType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('leader', PeopleAutocompleteChoiceType::class, [
                'required' => true,
                'label' => 'intervention.fields.leader',
            ])
            ->add('operators', PeopleAutocompleteChoiceType::class, [
                'multiple' => true,
                'label' => 'intervention.fields.operators',
            ])
            ->add('customerServiceRecord', CustomerServiceRecordChoiceType::class, [
                'required' => true,
                'label' => 'intervention.fields.csr',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary text-capitalize'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_service_record',
        ]);
    }
}
