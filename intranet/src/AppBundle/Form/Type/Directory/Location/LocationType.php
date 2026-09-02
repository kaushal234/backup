<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Location;

use ApiBundle\Form\Type\ResourceCollectionType;
use AppBundle\Form\Type\AddressType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Finance\CurrencyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LocationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'directory.location.fields.name',
                'required' => true,
            ])
            ->add('company', TextType::class, [
                'label' => 'directory.location.fields.company',
                'required' => true,
            ])
            ->add('erp', IntegerType::class, [
                'label' => 'directory.location.fields.erp',
                'required' => false,
            ])
            ->add('erpInLN', CheckboxType::class, [
                'label' => 'directory.location.fields.erpInLN',
                'required' => false,
            ])
            ->add('currency', CurrencyChoiceType::class, [
                'required' => false,
            ])
            ->add('capability', LocationCapabilityType::class, [
                'label' => 'directory.location_capability.name',
                'required' => false,
            ])
            ->add('contact', LocationContactType::class, [
                'label' => 'directory.location_contact.name',
                'required' => false,
            ])
            ->add('state', LocationStateType::class, [
                'label' => 'directory.location_state.name',
                'required' => false,
            ])
            ->add('address', AddressType::class, [
                'label' => 'address.name',
                'required' => false,
            ])
            ->add('domain', TextType::class, [
                'label' => 'directory.location.fields.domain',
                'required' => false,
            ])
            ->add('internalNetworkAddress', TextType::class, [
                'label' => 'directory.location.fields.internalNetworkAddress',
                'required' => false,
            ])
            ->add('businessUnit', ResourceCollectionType::class, [
                'label' => 'directory.location.fields.businessUnit',
                'required' => false,
                'resource' => 'business_units',
                'property' => 'name',
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('network', ResourceCollectionType::class, [
                'label' => 'directory.location.fields.network',
                'required' => false,
                'resource' => 'networks',
                'property' => 'name',
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('juridicalLocation', ResourceCollectionType::class, [
                'label' => 'directory.location.fields.juridicalLocation',
                'required' => false,
                'resource' => 'juridical_locations',
                'property' => 'name',
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('representative', PeopleAutocompleteChoiceType::class, [
                'label' => 'directory.location.fields.representative',
                'required' => false,
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('timeZone', ChoiceType::class, [
                'label' => 'directory.location.fields.timeZone',
                'required' => true,
                'choices' => $this->getTimeZoneList(),
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('erpSoftware', ChoiceType::class, [
                'label' => 'directory.location.fields.erp_software_form',
                'required' => false,
                'choices' => [
                    'LN' => 'LN',
                    'P21' => 'P21',
                    'PROGINOV' => 'PROGINOV',
                    'INFOR EAM' => 'INFOR EAM',
                ],
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('publicWebsite', TextType::class, [
                'label' => 'directory.location.fields.public_website',
                'required' => false,
            ])
        ;
    }

    public function getTimeZoneList(): array
    {
        return array_combine(
            \DateTimeZone::listIdentifiers(),
            \DateTimeZone::listIdentifiers(\DateTimeZone::ALL, 'EN')
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_location';
    }
}
