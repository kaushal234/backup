<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

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
class ManualPrinterChoiceType extends AbstractType
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
        $resolver->setDefaults(
            [
                'key' => '@id',
                'name' => 'name',
                'label' => 'fields.name',
                'placeholder' => 'support.printer.fields.make_selection',
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    $collection = $this->dataProvider->findAll(
                        'support/manual_printers',
                        [],
                        ['companyName']
                    );

                    $choices = [];
                    foreach ($collection as $printer) {
                        $value = \sprintf('%s / %s - %s / %s', $printer['companyName'], $printer['firstname'], $printer['lastname'], $printer['email']);
                        $choices[$value] = $printer[$options['key']];
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
