<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use AppBundle\Form\Type\LanguageChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EquipmentRecordAddManualType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('language', LanguageChoiceType::class, [
                'choice_translation_domain' => false,
                'label' => 'support.manual.fields.language',
            ])
            ->add('status', ChoiceType::class, [
                'choice_translation_domain' => false,
                'choices' => [
                    'RELEASED' => 'RELEASED',
                    'PRELIMINARY' => 'PRELIMINARY',
                ],
                'label' => 'support.manual.fields.status',
            ])
            ->add('cbom_submit', SubmitType::class, [
                'label' => $options['publishable'] ? 'support.equipment_record.cbom.validate' : 'support.equipment_record.cbom.submit',
                'attr' => ['class' => 'btn btn-danger'],
            ])
        ;

        if ($options['publishable']) {
            $builder
                ->add('secondaryEquipmentRecords', EquipmentRecordAddManualSecondaryEquipmentRecordChoiceType::class, [
                    'label' => 'support.manual.fields.Specify other equipments',
                    'help' => 'support.manual.fields.Specify other equipments help',
                    'expanded' => false,
                    'multiple' => true,
                    'required' => false,
                    'filters' => [
                        'exclude' => $options['equipmentRecord']['@id'],
                        'model' => $options['equipmentRecord']['model'],
                        'manufacturerLocation' => $options['equipmentRecord']['manufacturerLocation']['@id'],
                    ],
                ])
                ->add('publishable_submit', SubmitType::class, [
                    'label' => 'support.equipment_record.publish.submit',
                    'attr' => ['class' => 'btn btn-warning'],
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
            'publishable' => false,
            'equipmentRecord' => null,
        ]);
    }
}
