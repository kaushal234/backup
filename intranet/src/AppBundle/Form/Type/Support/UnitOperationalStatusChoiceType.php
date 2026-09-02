<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UnitOperationalStatusChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
    ) {
    }

    /**
     * {@inheritdoc}
     */
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
            'key' => '@id',
            'choice_translation_domain' => false,
            'choices' => function (Options $options): array {
                $collection = $this->dataProvider->findAll('unit_operational_statuses');

                $choices = [];
                foreach ($collection as $unitOperationalStatus) {
                    $value = \sprintf('%s - %s', $unitOperationalStatus['description'], $unitOperationalStatus['name']);
                    $choices[$value] = $unitOperationalStatus[$options['key']];
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
