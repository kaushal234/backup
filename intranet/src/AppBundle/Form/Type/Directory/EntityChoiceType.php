<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Doctrine\Inflector\InflectorFactory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class EntityChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
    ) {
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
            'choice_translation_domain' => false,
            'choices' => function (Options $options) {
                $inflector = InflectorFactory::create()->build();
                $collections = ['Divisions' => [], 'SubDivisions' => [], 'Regions' => [], 'Business Units' => []];
                foreach (array_keys($collections) as $entity) {
                    $results = $this->dataProvider->findAll($inflector->tableize(str_replace(' ', '', $entity)), [], ['name']);
                    foreach ($results as $result) {
                        $collections[$entity][$result['name']] = $result['@id'];
                    }
                }

                return ['ALL' => ''] + $collections;
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
