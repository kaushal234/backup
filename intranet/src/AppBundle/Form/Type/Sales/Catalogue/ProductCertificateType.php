<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Support\EmissionRatingChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductCertificateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $certificate = $builder->getData();
        $builder
            ->add('product', ProductAutocompleteChoiceType::class, [
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
            ])
            ->add('emissionRating', EmissionRatingChoiceType::class, [
                'label' => 'sales_forecasts.fields.tier',
                'translation_domain' => 'sales_forecasts',
                'required' => false,
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'directory.department.fields.factory',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('expiredAt', DatePickerType::class, [
                'label' => 'catalogue.certificates.fields.expired_at',
                'required' => false,
            ])
            ->add('expectedAt', DatePickerType::class, [
                'label' => 'catalogue.certificates.fields.expected_at',
                'required' => false,
            ])
            ->add('engineeringActivityProcess', NumberType::class, [
                'label' => 'catalogue.certificates.fields.eap_number',
                'required' => false,
            ])
            ->add('url', TextType::class, [
                'label' => 'customers.fields.url',
                'translation_domain' => 'sales_customers',
                'required' => false,
            ])
            ->add('testReportNumber', TextType::class, [
                'label' => 'catalogue.certificates.fields.test_report_number',
                'required' => false,
            ])
            ->add('announcementCertificateNumber', TextType::class, [
                'label' => 'catalogue.certificates.fields.announcement_number',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'general_fields.description',
                'translation_domain' => 'surveys',
                'required' => false,
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'catalogue.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'catalogue',
            'csrf_protection' => false,
        ]);
    }
}
