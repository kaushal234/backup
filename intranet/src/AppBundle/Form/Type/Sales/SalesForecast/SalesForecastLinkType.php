<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\SalesForecast;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SalesForecastLinkType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $filters = [
            'asm' => $options['salesForecast']['asm']['@id'],
            'buyer' => $options['salesForecast']['buyer']['@id'],
            'status' => ['DELAYED', 'IN_PROGRESS', 'BUDGET'],
        ];

        if (isset($options['salesForecast']['sso']) && null !== $options['salesForecast']['sso']) {
            $filters = [...$filters, 'sso' => $options['salesForecast']['sso']['@id']];
        }

        if (isset($options['salesForecast']['endUser']) && null !== $options['salesForecast']['endUser']) {
            $filters = [...$filters, 'endUser' => $options['salesForecast']['endUser']['@id']];
        }

        if (isset($options['salesForecast']['thirdParty']) && null !== $options['salesForecast']['thirdParty']) {
            $filters = [...$filters, 'thirdParty' => $options['salesForecast']['thirdParty']['@id']];
        }

        if (isset($options['salesForecast']['country']) && null !== $options['salesForecast']['country']) {
            $filters = [...$filters, 'country' => $options['salesForecast']['country']['@id']];
        }

        $builder
            ->add('salesForecasts', SalesForecastChoiceType::class, [
                'required' => true,
                'label' => false,
                'csrf_protection' => false,
                'multiple' => true,
                'filters' => $filters,
                'excluded' => $options['masterSalesForecastExcluded'],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'sales_forecasts.actions.link',
                'attr' => ['class' => 'btn btn-info'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_forecasts',
            'csrf_protection' => false,
            'masterSalesForecastExcluded' => [],
            'salesForecast' => [],
        ]);
    }
}
