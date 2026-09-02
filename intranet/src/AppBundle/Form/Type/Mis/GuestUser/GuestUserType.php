<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\GuestUser;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\ModuleExtendedAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Premise\PremiseAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GuestUserType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options) {
            if (!$options['add']) {
                return;
            }

            $data = $event->getData();

            if (empty($data['businessUnit'])) {
                $data['businessUnit'] = $options['default_business_unit'];
            }

            if (empty($data['premise'])) {
                $data['premise'] = $options['default_premise'];
            }

            $event->setData($data);
        });

        $builder
            ->add('lastname', TextType::class, [
                'label' => 'directory.people.fields.lastname',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'directory.people.fields.firstname',
            ])
            ->add('username', TextType::class, [
                'label' => 'directory.user.fields.username',
            ])
            ->add('email', TextType::class, [
                'label' => 'directory.people.fields.email',
            ])
            ->add('businessUnit', BusinessUnitAutocompleteChoiceType::class, [
                'label' => 'directory.business_unit.name',
            ])
            ->add('premise', PremiseAutocompleteChoiceType::class, [
                'label' => 'directory.premise.title',
            ])
            ->add('enableAt', DatePickerType::class, [
                'label' => 'directory.people.fields.enable_at',
                'help' => 'MM/DD/YYYY : IMPORTANT! ACCOUNT ACTIVATED FROM THIS DATE',
                'restrictions' => [
                    'minDateStr' => '-1 day',
                ],
            ])
            ->add('needsCollaborationAccess', ChoiceType::class, [
                'label' => 'directory.people.fields.needs_collaboration_access',
                'choices' => [
                    'Yes' => true,
                    'No' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('modules', ModuleExtendedAutocompleteChoiceType::class, [
                'label' => 'directory.people.fields.module',
                'template' => '{{ name }} - {{ shortDescription }}',
                'required' => false,
                'multiple' => true,
            ])
            ->add('plannedDisableAt', DatePickerType::class, [
                'label' => 'directory.people.fields.planned_disable_at',
                'help' => 'MM/DD/YYYY',
                'restrictions' => [
                    'minDateStr' => '+ 1 month',
                    'maxDateStr' => '+ 1 year',
                ],
                'defaultDate' => new \DateTime('+ 1 year'),
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary'],
            ]);

        if (!$options['add']) {
            $builder
                ->add('supervisor', PeopleAutocompleteChoiceType::class, [
                    'label' => 'directory.people.fields.supervisor',
                ])
                ->remove('enableAt');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
            'add' => true,
            'default_business_unit' => null,
            'default_premise' => null,
        ]);
    }
}
