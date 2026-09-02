<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Purchasing\SalesOrderReport;

use AppBundle\Form\Type\Directory\Location\ERPLocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesOrderReportFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('erp', ERPLocationChoiceType::class, [
                'placeholder' => 'make_selection',
                'translation_domain' => 'messages',
                'key' => 'erp',
                'data' => $options['erp'] ?: null,
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'button.filter',
                'attr' => ['class' => 'btn btn-primary text-uppercase'],
            ])

            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'csrf_protection' => false,
        ]);
        $resolver->setDefined(['erp']);
    }
}
