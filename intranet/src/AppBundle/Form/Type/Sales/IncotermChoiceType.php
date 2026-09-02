<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales;

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
class IncotermChoiceType extends AbstractType
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
            'key' => '@id',
            'filters' => [],
            'extra_choices' => [],
            'choice_translation_domain' => false,
            'placeholder' => 'equipment_shipping_record.fields.incoterm_placeholder',
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                $collection = $this->dataProvider->findAll(
                    'sales/incoterms',
                    $filters,
                    ['code']
                );

                $choices = [];
                foreach ($collection as $incoterm) {
                    $value = \sprintf('%s - %s', $incoterm['code'], $incoterm['description']);
                    $choices[$value] = $incoterm[$options['key']];
                }

                return array_merge($options['extra_choices'], $choices);
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
