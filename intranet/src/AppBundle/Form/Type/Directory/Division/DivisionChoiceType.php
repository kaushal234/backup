<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\Division;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class DivisionChoiceType extends AbstractType
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
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'label' => 'menu.division.title',
            'translation_domain' => 'messages',
            'key' => '@id',
            'filters' => [],
            'choice_translation_domain' => false,
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                $collection = $this->dataProvider->findAll(
                    'divisions',
                    $filters,
                    ['name']
                );

                $choices = [];
                foreach ($collection as $division) {
                    $choices[$division['name']] = $division[$options['key']];
                }

                return $choices;
            },
        ])->addNormalizer('filters', static fn (Options $options, $value) => $value + ['pagination' => false]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
