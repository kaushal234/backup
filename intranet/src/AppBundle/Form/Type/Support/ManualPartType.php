<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManualPartType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'label' => 'support.manual_documents.fields.position',
            ])
            ->add('partNumber', TextType::class, [
                'label' => 'support.manual_document_parts.fields.part_number',
            ])
            ->add('quantity', NumberType::class, [
                'attr' => ['min' => 0],
                'label' => 'support.manual_document_parts.fields.quantity',
            ])
            ->add('unitOfMeasure', TextType::class, [
                'label' => 'support.manual_document_parts.fields.unit_of_measure',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'support.manual_documents.fields.description',
                'attr' => ['rows' => 1],
            ])
            ->add('otherDescription', TextareaType::class, [
                'label' => 'support.manual_documents.fields.other_description',
                'attr' => ['rows' => 1],
                'required' => false,
            ])
            ->add('preventive', CheckboxType::class, [
                'label' => 'support.manual_document_parts.fields.preventive',
                'required' => false,
            ])
            ->add('maintenance', CheckboxType::class, [
                'label' => 'support.manual_document_parts.fields.maintenance',
                'required' => false,
            ])
            ->add('overhaul', CheckboxType::class, [
                'label' => 'support.manual_document_parts.fields.overhaul',
                'required' => false,
            ])
            ->add('critical', CheckboxType::class, [
                'label' => 'support.manual_document_parts.fields.critical',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
        ]);
    }
}
