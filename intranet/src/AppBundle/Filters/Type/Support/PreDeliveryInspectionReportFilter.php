<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Support;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PreDeliveryInspectionReportFilter extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('startDate', TextType::class, [
                'property_path' => '[options][startDate]',
                'label' => 'fields.from',
                'translation_domain' => 'messages',
                'required' => false,
                'by_reference' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd-mm-yyyy',
                ],
            ])
            ->add('endDate', TextType::class, [
                'property_path' => '[options][endDate]',
                'label' => 'fields.to',
                'translation_domain' => 'messages',
                'required' => false,
                'by_reference' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'dd-mm-yyyy',
                ],
            ])
            ->add('product', ProductAutocompleteChoiceType::class, [
                'property_path' => '[options][products]',
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
                'required' => false,
                'multiple' => true,
            ])
            ->add('productTypes', ProductTypeAutocompleteChoiceType::class, [
                'property_path' => '[options][productTypes]',
                'label' => 'catalogue.type.product_type',
                'translation_domain' => 'catalogue',
                'required' => false,
                'multiple' => true,
            ])
            ->add('sso', SSOChoiceType::class, [
                'property_path' => '[options][ssos]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('manufacturerLocation', FactoryChoiceType::class, [
                'property_path' => '[options][factories]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary text-capitalize'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
