<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Sales;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LeadTimeFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('factory', FactoryChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('productType', ProductTypeAutocompleteChoiceType::class, [
                'label' => 'catalogue.family.type',
                'property_path' => '[productFamily.productType]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'catalogue',
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
