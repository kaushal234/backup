<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Purchasing\BusinessPartner\IONBusinessPartnerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeChoiceType;
use AppBundle\Form\Type\Sales\Competitor\CompetitorAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\CustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\MarketIntelligence\MarketIntelligenceTypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MarketIntelligenceFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('poster', PeopleAutocompleteChoiceType::class, [
                'label' => 'market_intelligence.fields.poster',
                'translation_domain' => 'market_intelligence',
                'required' => false,
            ])
            ->add('customers', CustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
                'translation_domain' => 'demo',
                'required' => false,
            ])
            ->add('productTypes', ProductTypeChoiceType::class, [
                'label' => 'catalogue.type.product_type',
                'translation_domain' => 'catalogue',
                'required' => false,
            ])
            ->add('competitors', CompetitorAutocompleteChoiceType::class, [
                'label' => 'competitors.name',
                'translation_domain' => 'sales_competitors',
                'required' => false,
            ])
            ->add('suppliers', IONBusinessPartnerAutocompleteChoiceType::class, [
                'label' => 'market_intelligence.fields.suppliers',
                'translation_domain' => 'market_intelligence',
                'required' => false,
                'id_key' => '[name]',
            ])
            ->add('type', MarketIntelligenceTypeChoiceType::class, [
                'label' => 'market_intelligence.fields.mim_type',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'market_intelligence',
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
