<?php

declare(strict_types=1);

namespace ApiBundle\Form\Type;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ResourceCollectionType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
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
        $resolver->setRequired(['resource', 'property']);
        $resolver->setDefaults([
            'filters' => [],
            'orders' => null,
            'choice_translation_domain' => false,
            'choices' => function (Options $options) {
                $collection = $this->dataProvider->findAll(
                    $options['resource'],
                    $options['filters'],
                    null === $options['orders'] ? [$options['property']] : $options['orders']
                );

                $choices = [];
                foreach ($collection as $object) {
                    $choices[$object[$options['property']]] = $object['@id'];
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

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'api_resource_collection';
    }
}
