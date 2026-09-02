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

class EquipmentRecordAddManualSecondaryEquipmentRecordChoiceType extends AbstractType
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
            'choice_translation_domain' => false,
            'filters' => [
                'pagination' => false,
                'normalization_groups_override' => ['equipment_list'],
            ],
            'name_formatter' => static fn ($equipment) => $equipment['serialNumber'],
            'choices' => function (Options $options) {
                $filters = $options['filters'];
                $collection = $this->dataProvider->findAll(
                    EquipmentSerialsController::EQUIPMENT_RECORD_URL,
                    $filters,
                    ['serialNumber' => 'ASC']
                );

                $choices = [];
                foreach ($collection as $equipment) {
                    $value = $equipment['serialNumber'];
                    $choices[$value] = $equipment[$options['key']];
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
