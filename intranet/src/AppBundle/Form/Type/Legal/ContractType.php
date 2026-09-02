<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Legal;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitChoiceType;
use AppBundle\Form\Type\Directory\Division\DivisionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Location\RegionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Premise\PremiseAutocompleteChoiceType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('shortDescription', TextType::class, [
                'label' => 'legal.fields.title',
                'attr' => [
                    'placeholder' => 'legal.placeholder.shortDescription',
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'legal.fields.description',
            ])
            ->add('startDate', DatePickerType::class, [
                'defaultDate' => new \DateTime(),
                'label' => 'service.maintenance_contract.fields.start_date',
                'translation_domain' => 'service',
            ])
            ->add('expirationDate', DatePickerType::class, [
                'defaultDate' => new \DateTime(),
                'label' => 'service.maintenance_contract.fields.expiration_date',
                'translation_domain' => 'service',
                'required' => false,
            ])
            ->add('indefinitePeriodType', CheckboxType::class, [
                'label' => 'legal.fields.indefinite_period_type',
                'required' => false,
            ])
            ->add('renewalPeriod', IntegerType::class, [
                'label' => 'legal.fields.renewal_period',
                'required' => false,
            ])
            ->add('renewalUnit', ChoiceType::class, [
                'label' => 'legal.fields.renewal_unit',
                'required' => false,
                'placeholder' => 'legal.placeholder.renewal_unit',
                'choices' => [
                    'DAY' => 'DAY',
                    'MONTH' => 'MONTH',
                    'YEAR' => 'YEAR',
                ],
            ])
            ->add('externalParty', TextType::class, [
                'label' => 'legal.fields.external_party',
                'required' => false,
                'attr' => [
                    'placeholder' => 'legal.placeholder.external_party',
                ],
            ])
            ->add('internalParty', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'label' => 'legal.fields.internal_party',
                'required' => false,
                'attr' => [
                    'placeholder' => 'legal.placeholder.internal_party',
                ],
            ])
            ->add('otherPartySignatories', CollectionType::class, [
                'entry_type' => TextType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => true,
                'by_reference' => false,
                'label' => 'legal.fields.other_party_signatories',
                'required' => false,
                'attr' => [
                    'placeholder' => 'legal.placeholder.other_party_signatories',
                ],
            ])
            ->add('jurisdiction', TextType::class, [
                'label' => 'legal.fields.jurisdiction',
                'required' => false,
                'attr' => [
                    'placeholder' => 'legal.placeholder.jurisdiction',
                ],
            ])
            ->add('value', IntegerType::class, [
                'label' => 'legal.fields.contract_value',
                'required' => false,
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'required' => false,
            ])
            ->add('parentContract', ContractChoiceType::class, [
                'label' => 'legal.fields.parent_contract',
                'required' => false,
            ])
            ->add('subCategory', SubCategoryChoiceType::class, [
                'label' => 'legal.fields.sub_category',
                'placeholder' => '',
            ])
            ->add('divisions', DivisionAutocompleteChoiceType::class, [
                'label' => 'menu.division.title',
                'translation_domain' => 'messages',
                'multiple' => true,
                'required' => false,
            ])
            ->add('regions', RegionAutocompleteChoiceType::class, [
                'label' => 'menu.region.title',
                'translation_domain' => 'messages',
                'required' => false,
                'multiple' => true,
            ])
            ->add('premises', PremiseAutocompleteChoiceType::class, [
                'label' => 'directory.premise.title',
                'translation_domain' => 'directory',
                'required' => false,
                'multiple' => true,
            ])
            ->add('businessUnits', BusinessUnitChoiceType::class, [
                'label' => 'menu.business_unit.title',
                'translation_domain' => 'messages',
                'required' => false,
                'multiple' => true,
            ])
        ;
        if ($options['is_edit']) {
            $builder
                ->add('status', ChoiceType::class, [
                    'label' => 'demo.fields.status',
                    'translation_domain' => 'demo',
                    'choices' => [
                        'ACTIVE' => 'ACTIVE',
                        'EXPIRED' => 'EXPIRED',
                        'ARCHIVED' => 'ARCHIVED',
                    ], ])
                ->add('owner', PeopleAutocompleteChoiceType::class, [
                    'label' => 'shopfloor_columns.owner',
                    'translation_domain' => 'pio',
                ])
            ;
        }

        $builder->add('submit', SubmitType::class, ['attr' => ['class' => 'btn btn-info']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'legal',
            'is_edit' => false,
        ]);
    }
}
