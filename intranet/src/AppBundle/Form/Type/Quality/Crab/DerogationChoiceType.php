<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

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
class DerogationChoiceType extends AbstractType
{
    protected readonly DataProvider $dataProvider;

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
                'filters' => [
                    'status' => 'ACCEPTED',
                    'normalizationGroupsOverride' => ['derogation:list', 'crab:equipment_list', 'equipment_list'],
                ],
                'choice_translation_domain' => false,
                'choices' => function (Options $options) {
                    $collection = $this->dataProvider->findAll(
                        'quality/derogations',
                        $options['filters'],
                        ['id']
                    );

                    $choices = [];
                    foreach ($collection as $derogation) {
                        $equipmentRecords = [];
                        foreach ($derogation['crabs'] as $crab) {
                            $equipmentRecords[] = $crab['equipmentRecord']['serialNumber'];
                        }
                        $value = \sprintf('%s - %s - %s', $derogation['id'], $derogation['shortDescription'], implode(', ', $equipmentRecords));
                        $choices[$value] = $derogation[$options['key']];
                    }

                    return array_merge(['' => ''], $choices);
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
