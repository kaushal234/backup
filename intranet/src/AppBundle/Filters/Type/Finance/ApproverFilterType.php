<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Finance;

use AppBundle\Form\Type\Directory\Location\ERPLocationChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ApproverFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', ERPLocationChoiceType::class, [
                'label' => 'meeting.fields.location',
                'translation_domain' => 'meeting',
            ])
            ->add('analyticalDimension', TextType::class, [
                'label' => 'finance.approver.analytical_dimension',
            ])
            ->add('supplier', TextType::class, [
                'label' => 'finance.approver.supplier',
            ])
            ->add('assignee', PeopleAutocompleteChoiceType::class, [
                'label' => 'tasks.assignee',
                'translation_domain' => 'messages',
            ])
            ->add('assignor', PeopleAutocompleteChoiceType::class, [
                'label' => 'finance.approver.assignor',
            ])
            ->add('paymentBlocked', CheckboxType::class, [
                'required' => false,
                'label' => 'finance.approver.payment_blocked',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'attr' => ['class' => 'btn btn-info'],
                'translation_domain' => 'demo',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'finance',
            'csrf_protection' => false,
        ]);
    }
}
