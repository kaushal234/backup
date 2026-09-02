<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ForecastClosure;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForecastClosureAddCollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = [];

        foreach ($options['status'] as $status) {
            $data[] = ['status' => $status];
        }

        $builder
            ->add('forecastClosures', CollectionType::class, [
                'entry_type' => ForecastClosureAddType::class,
                'entry_options' => ['status' => array_combine($options['status'], $options['status']), 'sales_forecast' => $options['sales_forecast']],
                'data' => $data,
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
            'translation_domain' => 'forecast_closures',
            'allow_extra_fields' => true,
            'status' => [],
            'sales_forecast' => null,
            'csrf_protection' => false,
        ]);
    }
}
