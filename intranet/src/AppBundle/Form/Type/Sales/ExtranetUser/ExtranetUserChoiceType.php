<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class ExtranetUserChoiceType extends AbstractType
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
        $resolver->setDefaults([
            'key' => '@id',
            'filters' => [
                'extranetUserProfile.archived' => 0,
                'hidden' => 0,
                'normalization_groups_override' => ['extranet_user_list'],
            ],
            'orders' => [],
            'name_formatter' => static fn ($extranetUser): string => \sprintf('%s %s, %s', $extranetUser['lastname'], $extranetUser['firstname'], $extranetUser['username']),
            'choices' => function (Options $options) {
                $collection = $this->dataProvider->findAll(
                    'sales/extranet_users',
                    $options['filters'],
                    $options['orders']
                );

                $choices = [];
                foreach ($collection as $extranetUser) {
                    $value = \sprintf('%s %s, %s', $extranetUser['lastname'], $extranetUser['firstname'], $extranetUser['username']);
                    $choices[$value] = $extranetUser[$options['key']];
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
        return 'app_extranet_user_choice';
    }
}
