<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

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
class ExtranetUserGroupChoiceType extends AbstractType
{
    private readonly DataProvider $dataProvider;

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
        $resolver->setDefaults(
            [
                'key' => '@id',
                'filters' => [],
                'description_only' => false,
                'choices' => function (Options $options) {
                    $collection = $this->dataProvider->findAll(
                        'sales/extranet_user_groups',
                        $options['filters'],
                        ['name', 'description']
                    );

                    $choices = [];
                    foreach ($collection as $xuGroups) {
                        $value = false === $options['description_only'] ? \sprintf('%s: %s', $xuGroups['name'], $xuGroups['description']) : $xuGroups['description'];
                        $choices[$value] = $xuGroups[$options['key']];
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
        return SelectFormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_extranet_user_group_choice';
    }
}
