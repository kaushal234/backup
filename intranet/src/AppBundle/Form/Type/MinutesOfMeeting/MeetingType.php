<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\MinutesOfMeeting;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\ExtranetUser\ExtranetUserAutocompleteChoiceType;
use AppBundle\Form\Type\TextAreaEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $meeting = $builder->getData();
        $isCreation = null === $meeting;

        $builder
            ->add('title', TextType::class, [
                'label' => 'meeting.fields.title',
            ])

            ->add('description', TextAreaEditorType::class, [
                'label' => 'meeting.fields.description',
            ])

            ->add('location', TextType::class, [
                'label' => 'meeting.fields.location',
            ])
            ->add('meetingDate', DatePickerType::class, [
                'label' => 'meeting.fields.meetingDate',
            ])

            ->add('attendees', PeopleAutocompleteChoiceType::class, [
                'label' => 'meeting.fields.attendees',
                'required' => false,
                'multiple' => true,
                'placeholder' => null,
                'data' => $meeting ? $meeting['attendees'] : null,
            ])

            ->add('businessUnits', BusinessUnitChoiceType::class, [
                'label' => 'meeting.fields.businessUnits',
                'required' => false,
                'multiple' => true,
                'placeholder' => null,
                'data' => $meeting ? $meeting['businessUnits'] : null,
            ])

            ->add('contacts', CollectionType::class, [
                'entry_type' => MeetingContactType::class,
                'label' => false,
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,

                'data' => $meeting ? $meeting['contacts'] : null,
            ])

            ->add('customers', CustomerAutocompleteChoiceType::class, [
                'label' => 'market_intelligence.fields.customers',
                'translation_domain' => 'market_intelligence',
                'required' => false,
                'multiple' => true,
                'placeholder' => null,
                'data' => $meeting ? $meeting['customers'] : null,
            ])

            ->add('competitors', CompetitorAutocompleteChoiceType::class, [
                'label' => 'market_intelligence.fields.competitors',
                'translation_domain' => 'market_intelligence',
                'required' => false,
                'multiple' => true,
                'placeholder' => null,
            ])

            ->add('productTypes', ProductTypeAutocompleteChoiceType::class, [
                'label' => 'market_intelligence.fields.productTypes',
                'translation_domain' => 'market_intelligence',
                'required' => false,
                'multiple' => true,
                'placeholder' => null,
            ])

            ->add('customerContacts', ExtranetUserAutocompleteChoiceType::class, [
                'label' => 'meeting.fields.customer_contacts',
                'required' => false,
                'multiple' => true,
                'query' => [
                    'order' => [
                        'lastname' => 'ASC',
                        'firstname' => 'ASC',
                    ],
                    'hidden' => 0,
                    'extranetUserProfile.archived' => 0,
                    'normalization_groups_override' => ['extranet_user_list'],
                    'pagination' => false,
                ],
                'data' => $meeting ? $meeting['customerContacts'] : null,
            ])

            ->add('confidential', CheckboxType::class, [
                'label' => 'meeting.fields.confidential',
                'required' => false,
            ])

            ->add('fullDescription', TextAreaEditorType::class, [
                'label' => 'meeting.fields.fullDescription',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-info',
                    'style' => 'text-transform: uppercase;',
                ],
            ]);

        if ($isCreation) {
            $builder->add('quick', CheckboxType::class, [
                'label' => 'meeting.fields.quickMeeting',
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'meeting',
        ]);
    }
}
