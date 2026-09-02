<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Purchasing\ShortageReport;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShortageReportFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('erp', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'property_path' => '[erp]',
                'placeholder' => 'make_selection',
                'translation_domain' => 'messages',
                'required' => true,
                'key' => 'erp',
                'filters' => ['erpInLN' => true],
            ])
            ->add('sequenceNumber', TextType::class, [
                'label' => 'shortage_report.fields.sequence_number',
                'property_path' => '[sequenceNumber]',
                'required' => true,
                'auto_initialize' => 1,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'manufacturing',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_shortage_filters';
    }
}
