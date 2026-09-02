<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use ApiBundle\Model\ApiData;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated peopleChoiceType is deprecated, use PeopleAutocompleteChoiceType instead
 * @see PeopleAutocompleteChoiceType
 */
class PeopleChoiceType extends AbstractType
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
            'exclude' => [],
            'key' => '@id',
            'filters' => [],
            'disable_default_filters' => false,
            'choice_translation_domain' => false,
            'extra_choices' => [],
            'choices' => function (Options $options): array {
                $collection = $this->dataProvider->findAll(
                    'people',
                    $options['filters'],
                    ['lastname', 'firstname']
                );

                $excluded = [];
                foreach ($options['exclude'] as $user) {
                    if (\is_array($user) || $user instanceof ApiData) {
                        if (!empty($user['@id'])) {
                            $excluded[] = $user['@id'];
                        }
                    } else {
                        $excluded[] = $user;
                    }
                }

                $choices = [];
                foreach ($collection as $user) {
                    if (\in_array($user['@id'], $excluded, true)) {
                        continue;
                    }

                    $value = \sprintf('%s, %s - %s', $user['lastname'], $user['firstname'], $user['email']);

                    if (isset($options['filters']['normalization_groups_override']) && \in_array('region:list', $options['filters']['normalization_groups_override'], true)) {
                        if (isset($user['businessUnit']['region']['name'])) {
                            $value = \sprintf('%s - %s', $value, $user['businessUnit']['region']['name']);
                        }
                    }
                    $choices[$value] = $user[$options['key']];
                }

                return array_merge($options['extra_choices'], $choices);
            },
        ])->addNormalizer('filters', static fn (Options $options, $value) => $options['disable_default_filters']
            ? $value
            : $value +
            ['hidden' => 0, 'disabled' => 0, 'pagination' => 0, 'normalization_groups_override' => ['people_list']]
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
        return 'app_people_choice';
    }
}
