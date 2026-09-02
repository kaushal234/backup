<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Common;

use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrinterChoiceType extends AbstractType
{
    /**
     * @var DataProvider
     */
    protected $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * {@inheritdoc}
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'key' => 'reference',
            'filters' => [],
            'choice_translation_domain' => false,
            'translation_domain' => 'messages',
            'label' => 'print.fields.printer',
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                $collection = $this->dataProvider->findAll(
                    'printers',
                    $filters,
                    ['name']
                );

                $choices = [];
                foreach ($collection as $printer) {
                    $choices[$printer['name']] = $printer[$options['key']];
                }

                return $choices;
            },
        ])->addNormalizer('filters', static fn (Options $options, $value) => $value + [
            'pagination' => false,
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
