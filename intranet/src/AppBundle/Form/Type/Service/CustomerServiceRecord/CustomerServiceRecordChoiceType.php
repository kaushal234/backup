<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord;

use AppBundle\DataPersister\Service\CustomerServiceRecordPersister;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class CustomerServiceRecordChoiceType extends AbstractType
{
    public function __construct(
        private readonly DataProvider $dataProvider,
    ) {
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
            'choice_translation_domain' => false,
            'extra_choices' => [],
            'choices' => function (Options $options): array {
                $collection = $this->dataProvider->findAll(
                    CustomerServiceRecordPersister::CUSTOMER_SERVICE_RECORD_URL,
                    $options['filters'],
                );

                $excluded = [];
                foreach ($options['exclude'] as $customerServiceRecord) {
                    if (\is_array($customerServiceRecord)) {
                        if (!empty($customerServiceRecord['@id'])) {
                            $excluded[] = $customerServiceRecord['@id'];
                        }
                    } else {
                        $excluded[] = $customerServiceRecord;
                    }
                }

                $choices = [];
                foreach ($collection as $customerServiceRecord) {
                    if (\in_array($customerServiceRecord['@id'], $excluded, true)) {
                        continue;
                    }
                    $value = \sprintf('#%s - %s', $customerServiceRecord['id'], $customerServiceRecord['title']);
                    $choices[$value] = $customerServiceRecord[$options['key']];
                }

                return array_merge($options['extra_choices'], $choices);
            },
        ])->addNormalizer('filters', static fn (Options $options, $value) => $value + [
            'pagination' => 0,
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
        return 'app_people_choice';
    }
}
