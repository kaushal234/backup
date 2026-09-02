<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Controller\Support\EquipmentSerialsController;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentRecordChoiceType extends AbstractType
{
    final public const ER_DEMO = '**DEMO**';
    final public const ER_STOCK = '**STOCK**';

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
                'pagination' => false,
                'normalization_groups_override' => ['equipment_list'],
            ],
            'collection' => [],
            'additionalFilters' => [],
            'orders' => [],
            'er_demo' => false,
            'product' => null,
            'choice_value_by' => '',
            'name_formatter' => static function ($equipment) {
                return \sprintf('%s / %s - %s', $equipment['type'], $equipment['model'], $equipment['serialNumber']);
            },
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                if (!empty($options['additionalFilters'])) {
                    $filters = array_merge($filters, $options['additionalFilters']);
                }
                if (false !== $options['er_demo']) {
                    $filters['buyer.name'] = [self::ER_DEMO, self::ER_STOCK];
                    $filters['product'] = $options['product'];
                }
                if ($options['collection']) {
                    $collection = $options['collection'];
                } else {
                    $collection = $this->dataProvider->findAll(
                        EquipmentSerialsController::EQUIPMENT_RECORD_URL,
                        $filters,
                        $options['orders']
                    );
                }

                $choices = [];
                foreach ($collection as $equipment) {
                    if ('serialNumber' === $options['choice_value_by']) {
                        $value = \sprintf('%s', $equipment['serialNumber']);
                        $choices[$value] = $equipment[$options['key']];
                    } else {
                        $value = \sprintf('%s / %s - %s', $equipment['type'], $equipment['model'], $equipment['serialNumber']);
                        $choices[$value] = $equipment[$options['key']];
                    }
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
        return 'app_equipment_record_choice';
    }
}
