<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use AppBundle\Form\Type\Parts\PartsNumberAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\FirstArticleQualification\FirstArticleQualificationAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\NonConformity\NonConformityAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CrabAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                'query' => [
                    'notShipped' => true,
                    'order' => [
                        'serialNumber' => 'ASC',
                    ],
                ],
            ])
            ->add('orderLine', IntegerType::class, [
                'label' => 'customers.menu.sol',
                'translation_domain' => 'sales_customers',
                'required' => false,
            ])
            ->add('nonConformity', NonConformityAutocompleteChoiceType::class, [
                'required' => false,
            ])
            ->add('department', CrabDepartmentChoiceType::class, [
                'label' => 'crab.fields.department',
                'translation_domain' => 'crab',
            ])
            ->add('code', CodeAutocompleteChoiceType::class, [
                'required' => true,
            ])
            ->add('firstArticleQualification', FirstArticleQualificationAutocompleteChoiceType::class, [
                'required' => false,
            ])
            ->add('category', CategoryChoiceType::class, [
                'required' => true,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'crab.fields.description',
                'translation_domain' => 'crab',
            ])
            ->add('mainFile', FileType::class, [
                'translation_domain' => 'file_type',
                'required' => false,
                'label' => 'file_type.file_upload',
            ])
            ->add('eapId', IntegerType::class, [
                'required' => false,
                'label' => 'crab.fields.eap_id',
                'translation_domain' => 'crab',
            ])
            ->add('part', PartsNumberAutocompleteChoiceType::class, [
                'label' => 'fields.part_number',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if ($options['addFromNonConformity']) {
            if (!empty($options['parts'])) {
                $builder
                    ->remove('part')
                    ->add('part', ChoiceType::class, [
                        'choices' => $options['parts'],
                        'label' => 'display.table.mrp.headers.part_number',
                        'translation_domain' => 'messages',
                    ])
                    ->remove('nonConformity')
                ;
            }
        }

        if (!empty($options['equipmentRecords'])) {
            $builder
                ->remove('equipmentRecord')
                ->add('equipmentRecord', ChoiceType::class, [
                    'choices' => $options['equipmentRecords'],
                    'label' => 'crab.fields.equipment_record',
                    'translation_domain' => 'crab',
                ]);
        }

        $options['addFromSalesOrderLine'] ? $builder->remove('equipmentRecord') : $builder->remove('orderLine');
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'user' => null,
            'csrf_protection' => false,
            'addFromNonConformity' => false,
            'parts' => [],
            'equipmentRecords' => [],
            'addFromSalesOrderLine' => false,
        ]);
    }
}
