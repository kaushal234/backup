<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Directory\Location\ServiceChoiceType;
use AppBundle\Form\Type\Directory\Location\SparePartsHubChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PartsRepresentativeAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\ServiceRepresentativeAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerRelationshipTeamFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', CustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'required' => false,
            ])
            ->add('salesRepresentative', ASMAutocompleteChoiceType::class, [
                'label' => 'customer_relationship_team.fields.sales_representative',
                'required' => false,
            ])
            ->add('partsRepresentative', PartsRepresentativeAutocompleteChoiceType::class, [
                'label' => 'customer_relationship_team.fields.parts_representative',
                'required' => false,
            ])
            ->add('serviceRepresentative', ServiceRepresentativeAutocompleteChoiceType::class, [
                'label' => 'customer_relationship_team.fields.service_representative',
                'required' => false,
            ])
            ->add('partsLocation', SparePartsHubChoiceType::class, [
                'label' => 'customer_relationship_team.fields.parts_location',
                'required' => false,
            ])
            ->add('serviceLocation', ServiceChoiceType::class, [
                'label' => 'customer_relationship_team.fields.service_location',
                'required' => false,
            ])
            ->add('erpLocation', SSOChoiceType::class, [
                'label' => 'customer_relationship_team.fields.erp_location',
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'customer_relationship_team',
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
