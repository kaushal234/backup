<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Catalogue;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ProductChoiceType extends AbstractType
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
            'allProducts' => false,
            'extra_choices' => [],
            'filters' => static function (Options $options) {
                $filters = [
                    'normalization_groups_override' => ['product_list'],
                ];

                if (false === $options['allProducts']) {
                    $filters['hidden'] = 0;
                }

                return $filters;
            },
            'choice_translation_domain' => false,
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                $collection = $this->dataProvider->findAll(
                    'sales/products',
                    $filters,
                    ['name']
                );

                $choices = [];
                foreach ($collection as $product) {
                    $value = $product['name'];
                    $choices[$value] = $product[$options['key']];
                }

                return $options['extra_choices'] + $choices;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
