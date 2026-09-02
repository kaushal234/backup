<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Finance;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExchangeRateDateFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('year', TextType::class, [
                'label' => 'forex.fields.by_year',
                'property_path' => '[year]',
                'required' => false,
                'by_reference' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'yyyy',
                    'data-date-min-view-mode' => 'years',
                    'data-date-end-date' => date('Y-m-d'),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'forex',
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
