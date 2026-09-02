<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\SupplierCorrectiveActionRequest;

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
class SupplierCorrectiveActionRequestChoiceType extends AbstractType
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
        $resolver->setDefaults([
            'key' => '@id',
            'filters' => [],
            'choice_translation_domain' => false,
            'parts' => [],
            'extra_choices' => [],
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                if (empty($options['parts'])) {
                    return [];
                }
                $filters['parts.partNumber'] = array_unique($options['parts']);
                $collection = $this->dataProvider->findAll(
                    'quality/supplier_corrective_action_requests',
                    $filters,
                    ['id']
                );

                $choices = [];
                foreach ($collection as $supplierCorrectiveActionRequest) {
                    $value = \sprintf('%s - %s', $supplierCorrectiveActionRequest['id'], $supplierCorrectiveActionRequest['shortDescription']);
                    $choices[$value] = $supplierCorrectiveActionRequest[$options['key']];
                }

                return $choices;
            },
        ])->addNormalizer('filters', static fn (Options $options, $value) => $value + [
            'normalizationGroupsOverride' => ['supplier_corrective_action_request:list'],
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
