<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\MinutesOfMeeting;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MeetingFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customers', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'survey.publication_form.customer',
                'translation_domain' => 'surveys',
                'required' => false,
                'multiple' => true,
            ])
            ->add('competitors', CompetitorAutocompleteChoiceType::class, [
                'label' => 'market_intelligence.fields.competitors',
                'translation_domain' => 'market_intelligence',
                'required' => false,
                'multiple' => true,
            ])
            ->add('productTypes', ProductTypeAutocompleteChoiceType::class, [
                'label' => 'catalogue.type.product_type',
                'translation_domain' => 'catalogue',
                'required' => false,
                'multiple' => true,
            ])
            ->add('status', SelectFormType::class, [
                'label' => 'demo.fields.status',
                'translation_domain' => 'demo',
                'required' => false,
                'multiple' => true,
                'choices' => [
                    'OPEN' => 'OPEN',
                    'RELEASED' => 'RELEASED',
                    'CLOSED' => 'CLOSED',
                ],
            ])
            ->add('businessUnits', BusinessUnitAutocompleteChoiceType::class, [
                'label' => 'meeting.fields.businessUnits',
                'required' => false,
                'multiple' => true,
            ])
            ->add('createdBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'fields.poster',
                'translation_domain' => 'messages',
                'required' => false,
                'multiple' => true,
            ])
            ->add('myTeam', CheckboxType::class, [
                'label' => 'meeting.filter.myteam',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'meeting',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}
