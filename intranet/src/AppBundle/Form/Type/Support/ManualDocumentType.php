<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ManualDocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', IntegerType::class, [
                'attr' => ['min' => 1],
                'label' => 'support.manual_documents.fields.position',
            ])
            ->add('factoryNumber', TextType::class, [
                'label' => 'support.manual_documents.fields.factory_number',
            ])
            ->add('revision', TextType::class, [
                'label' => 'support.manual_documents.fields.revision',
            ])
            ->add('category', ManualDocumentCategoryChoiceType::class, [
                'label' => 'support.manual_documents.fields.category',
                'manual_document_category_choices' => $options['manual_document_category_choices'],
            ])
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'ELEC SCHEM' => 'ELEC SCHEM',
                    'FLOW SCHEM' => 'FLOW SCHEM',
                    'HYD SCHEM' => 'HYD SCHEM',
                    'MANUAL SECTION' => 'MANUAL SECTION',
                    'MANUAL:APPENDIX' => 'MANUAL:APPENDIX',
                    'MANUAL:OEM LIT' => 'MANUAL:OEM LIT',
                    'MANUAL:SECTION' => 'MANUAL:SECTION',
                    'MANUAL:TOC' => 'MANUAL:TOC',
                    'PARTS DIAGRAM' => 'PARTS DIAGRAM',
                ],
                'label' => 'support.manual_documents.fields.type',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'support.manual_documents.fields.description',
                'attr' => ['rows' => 1],
            ])
            ->add('otherDescription', TextareaType::class, [
                'label' => 'support.manual_documents.fields.other_description',
                'required' => false,
                'attr' => ['rows' => 1],
            ])
        ;
        if ($options['show_manual_parts']) {
            $builder
                ->add('parts', CollectionType::class, [
                    'label' => false,
                    'entry_type' => ManualPartType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
            'show_manual_parts' => true,
            'manual_document_category_choices' => [],
        ]);
    }
}
