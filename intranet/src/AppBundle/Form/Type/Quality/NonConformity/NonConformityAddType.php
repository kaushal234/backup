<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\NonConformity;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\ImportanceFactorType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class NonConformityAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationChoiceType::class, [
                'label' => 'directory.department.fields.location',
                'translation_domain' => 'directory',
                'extra_choices' => ['' => ''],
                'erp_in_label' => true,
                'filters' => ['has_any_capability' => ['factory', 'sso', 'sparePartsHub']],
                'constraints' => [new NotBlank()],
            ])
            ->add('reportedBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'non_conformity.fields.reported_by',
                'translation_domain' => 'non_conformity',
                'constraints' => [new NotBlank()],
            ])
            ->add('hours', IntegerType::class, [
                'label' => 'non_conformity.fields.hours',
                'required' => false,
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'mis.modules.fields.short_desc',
                'translation_domain' => 'mis',
                'constraints' => [new NotBlank()],
            ])
            ->add('problem', TextareaType::class, [
                'label' => 'non_conformity.fields.problem',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
                'constraints' => [new NotBlank()],
            ])
            ->add('iFactor', ImportanceFactorType::class, [
                'label' => 'fields.ifactor',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'constraints' => [new NotBlank()],
            ])
            ->add('mainFile', FileType::class, [
                'required' => true,
                'translation_domain' => 'file_type',
                'label' => 'file_type.file_upload',
            ])
            ->add('investigation', TextareaType::class, [
                'label' => 'non_conformity.fields.investigation',
                'attr' => [
                    'style' => 'resize:vertical; height:150px',
                ],
                'required' => false,
            ])
            ->add('environmentalIssue', CheckboxType::class, [
                'label' => 'non_conformity.fields.environmental_issue',
                'attr' => ['class' => 'disable-field'],
                'required' => false,
            ])
            ->add('safety', CheckboxType::class, [
                'label' => 'non_conformity.fields.safety',
                'attr' => ['class' => 'disable-field'],
                'required' => false,
            ])
            ->add('rush', CheckboxType::class, [
                'label' => 'non_conformity.fields.rush',
                'required' => false,
            ])
            ->add('equipmentRecords', EquipmentRecordAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('products', ProductAutocompleteChoiceType::class, [
                'label' => 'catalogue.products.product',
                'translation_domain' => 'catalogue',
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'non_conformity.button.add_parts',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('no_parts_to_add', SubmitType::class, [
                'label' => 'non_conformity.button.no_parts',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if ($options['addFromCrab']) {
            $builder->add('importCrabPhoto', CheckboxType::class, [
                'label' => 'non_conformity.fields.add_from_crab',
                'data' => true,
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'non_conformity',
            'addFromCrab' => false,
        ]);
    }
}
