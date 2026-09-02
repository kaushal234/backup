<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Materials\Warehouse;

use ApiBundle\Iri\Iri;
use AppBundle\Form\Type\Directory\Location\WarehouseAutocompleteChoiceType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthlyActivitiesKPIFilterType extends AbstractType
{
    private readonly DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Not ideal but that's the rule in the command that inserts monthly activities data
        $mappings = $this->dataProvider->findAll('materials/warehouse/tasks_mappings');
        $locations = [];
        foreach ($mappings as $mapping) {
            $locations[] = Iri::id($mapping['location']);
        }

        $builder
            ->add('location', WarehouseAutocompleteChoiceType::class, [
                'required' => false,
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'capability.warehouse' => true,
                    'erpInLN' => true,
                    'id' => $locations,
                ],
            ])
            ->add('from', TextType::class, [
                'label' => 'forex.fields.applicated_on_after',
                'translation_domain' => 'forex',
                'property_path' => '[applicatedOn][after]',
                'required' => false,
                'by_reference' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'yyyy-mm',
                    'data-date-min-view-mode' => 'months',
                ],
            ])
            ->add('until', TextType::class, [
                'label' => 'forex.fields.applicated_on_before',
                'translation_domain' => 'forex',
                'property_path' => '[applicatedOn][before]',
                'required' => false,
                'by_reference' => true,
                'attr' => [
                    'class' => 'datepicker',
                    'data-provide' => 'datepicker',
                    'data-date-format' => 'yyyy-mm',
                    'data-date-min-view-mode' => 'months',
                ],
            ])
            ->add('fullTimeEquivalentMonthlyHours', IntegerType::class, [
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
            'method' => Request::METHOD_GET,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
