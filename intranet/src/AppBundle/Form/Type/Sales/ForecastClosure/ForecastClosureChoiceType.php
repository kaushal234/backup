<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ForecastClosure;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ForecastClosureChoiceType extends AbstractType
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new IrisResourceToIdTransformer());
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'key' => 'id',
            'filters' => [],
            'salesForecast' => null,
            'choice_translation_domain' => false,
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                $filters['salesForecast'] = $options['salesForecast'];
                $collection = $this->dataProvider->findAll(
                    'sales/forecast_closures',
                    $filters,
                    ['id' => 'DESC']
                );

                $choices = [];
                foreach ($collection as $fcr) {
                    $value = \sprintf('FCR#%s - %s', $fcr['id'], $fcr['status']);
                    $choices[$value] = $fcr[$options['key']];
                }

                return $choices;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
