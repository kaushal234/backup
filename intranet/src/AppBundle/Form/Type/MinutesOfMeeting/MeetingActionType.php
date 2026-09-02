<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\MinutesOfMeeting;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingActionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('assignee', PeopleAutocompleteChoiceType::class, [
                'required' => true,
                'label' => 'tasks.assignee',
            ])
            ->add('description', TextType::class, [
                'required' => true,
                'label' => 'tasks.description',
            ])
            ->add('dueDate', DatePickerType::class, [
                'required' => true,
                'label' => 'tasks.dueDate',
                'defaultDate' => new \DateTime('+14 days'),
                'restrictions' => [
                    'minDateStr' => 'now',
                ],
            ])
            ->add('escalationTrigger', IntegerType::class, [
                'required' => true,
                'label' => 'tasks.escalationTrigger',
                'data' => 30,
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'label' => 'tasks.description',
                'attr' => [
                    'style' => 'resize:vertical; height:200px',
                ],
            ])
            ->add('cc', PeopleAutocompleteChoiceType::class, [
                'label' => 'tasks.cc',
                'required' => false,
                'multiple' => true,
            ])
            ->add('internal', CheckboxType::class, [
                'label' => 'meeting.actions.confidential',
                'translation_domain' => 'meeting',
                'required' => false,
                'property_path' => '[metadata][internal]',
            ])
            ->add('customer', CustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'required' => false,
                'property_path' => '[metadata][customer]',
                'query' => [
                    'status' => ['APPROVED', 'PENDING RE-APPROVAL'],
                    'order' => ['name' => 'ASC'],
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'translation_domain' => 'demo',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
        ]);
    }
}
