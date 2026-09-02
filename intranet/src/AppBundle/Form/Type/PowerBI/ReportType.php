<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\PowerBI;

use AppBundle\Form\Type\Directory\GroupAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'fields.title',
            ])
            ->add('description', TextType::class, [
                'label' => 'fields.description',
                'required' => false,
            ])
            ->add('category', CategoryChoiceType::class, [
                'label' => 'fields.category',
            ])
            ->add('subCategories', SubCategoryChoiceType::class, [
                'label' => 'power_bi_report.fields.sub_categories',
                'multiple' => true,
                'expanded' => false,
                'required' => false,
            ])
            ->add('powerBiUuid', TextType::class, [
                'label' => 'power_bi_report.fields.powerBiUuid',
                'help' => 'power_bi_report.fields.powerBiUuid_help',
            ])
            ->add('groups', GroupAutocompleteChoiceType::class, [
                'label' => 'power_bi_report.fields.groups',
                'required' => false,
                'multiple' => true,
                'help' => 'power_bi_report.fields.groups_helper',
                'translation_domain' => 'messages',
            ])
        ;
    }
}
