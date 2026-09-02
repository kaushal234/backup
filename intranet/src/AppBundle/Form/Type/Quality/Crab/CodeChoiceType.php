<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

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
class CodeChoiceType extends AbstractType
{
    protected readonly DataProvider $dataProvider;

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
        $resolver->setDefaults(
            [
                'key' => '@id',
                'crab_code' => null,
                'filters' => [],
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    $collection = $this->dataProvider->findAll(
                        'quality/crab_codes',
                        [],
                        ['code']
                    );

                    $choices = [];
                    foreach ($collection as $code) {
                        $value = \sprintf('%s - %s', $code['code'], $code['description']);
                        $choices[$value] = $code[$options['key']];
                    }

                    return $choices;
                },
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
