<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality\FirstArticleQualification;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\People\BuyerAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\QualityPeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\FirstArticleQualification\FirstArticleQualificationTagChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductFamilyChoiceType;
use AppBundle\Manager\Quality\FirstArticleQualification\FirstArticleQualificationStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FirstArticleQualificationFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', SelectFormType::class, [
                'label' => 'fields.status',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'choices' => FirstArticleQualificationStatus::getStatuses(),
                'required' => false,
                'multiple' => true,
            ])
            ->add('iFactor', ChoiceType::class, [
                'label' => 'fields.ifactor',
                'placeholder' => 'make_selection',
                'translation_domain' => 'messages',
                'choice_translation_domain' => false,
                'choices' => [
                    'IF1' => 'IF1',
                    'IF10' => 'IF10',
                    'IF100' => 'IF100',
                    'IF1000' => 'IF1000',
                ],
                'required' => false,
            ])
            ->add('buyer', BuyerAutocompleteChoiceType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.buyer',
                'required' => false,
            ])
            ->add('owner', QualityPeopleAutocompleteChoiceType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.owner',
                'required' => false,
            ])
            ->add('poster', PeopleAutocompleteChoiceType::class, [
                'label' => 'fields.poster',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('location', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'required' => false,
                'property_path' => '[location]',
                'placeholder' => 'make_selection',
                'translation_domain' => 'messages',
            ])
            ->add('partNumber', TextType::class, [
                'translation_domain' => 'spq',
                'label' => 'spq.quotations.fields.part_number',
                'property_path' => '[partNumbers.number]',
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.description',
                'property_path' => '[partNumbers.description]',
                'required' => false,
            ])
            ->add('supplierName', TextType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.supplier.name',
                'required' => false,
            ])
            ->add('supplierNumber', TextType::class, [
                'translation_domain' => 'messages',
                'label' => 'fields.supplier.number',
                'required' => false,
            ])
            ->add('revision', TextType::class, [
                'translation_domain' => 'first_article_qualification',
                'label' => 'first_article_qualification.fields.revision',
                'property_path' => '[partNumbers.revision]',
                'required' => false,
            ])
            ->add('eap', NumberType::class, [
                'translation_domain' => 'sidebar',
                'label' => 'sidebar.engineering.eap',
                'required' => false,
            ])
            ->add('meap', NumberType::class, [
                'translation_domain' => 'sidebar',
                'label' => 'sidebar.engineering.meap',
                'required' => false,
            ])
            ->add('tags', FirstArticleQualificationTagChoiceType::class, [
                'property_path' => '[tags.name]',
                'required' => false,
            ])
            ->add('dueDate-after', DatePickerType::class, [
                'translation_domain' => 'first_article_qualification',
                'label' => 'first_article_qualification.fields.due_date.after',
                'property_path' => '[dueDate][after]',
                'required' => false,
            ])
            ->add('dueDate-before', DatePickerType::class, [
                'translation_domain' => 'first_article_qualification',
                'label' => 'first_article_qualification.fields.due_date.before',
                'property_path' => '[dueDate][before]',
                'required' => false,
            ])
            ->add('planDefinitionDueDate-after', DatePickerType::class, [
                'translation_domain' => 'first_article_qualification',
                'label' => 'first_article_qualification.fields.plan_definition_due_date.after',
                'property_path' => '[planDefinitionDueDate][after]',
                'required' => false,
            ])
            ->add('planDefinitionDueDate-before', DatePickerType::class, [
                'translation_domain' => 'first_article_qualification',
                'label' => 'first_article_qualification.fields.plan_definition_due_date.before',
                'property_path' => '[planDefinitionDueDate][before]',
                'required' => false,
            ])
            ->add('equipmentRecord', TextType::class, [
                'translation_domain' => 'service',
                'label' => 'service.equipment_record.fields.serial_number',
                'property_path' => '[equipmentRecords.serialNumber]',
                'required' => false,
            ])
            ->add('planApprovalStatus', ChoiceType::class, [
                'label' => 'first_article_qualification.fields.plan_approval_status',
                'translation_domain' => 'first_article_qualification',
                'choices' => FirstArticleQualificationStatus::getPlanStatuses(),
                'choice_translation_domain' => false,
                'required' => false,
            ])
            ->add('createdAt-before', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.createdAt.before',
                'property_path' => '[createdAt][before]',
                'required' => false,
            ])
            ->add('createdAt-after', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.createdAt.after',
                'property_path' => '[createdAt][after]',
                'required' => false,
            ])
            ->add('completedAt-before', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.completedAt.before',
                'property_path' => '[completedAt][before]',
                'required' => false,
            ])
            ->add('noOpenTasks', CheckboxType::class, [
                'label' => 'first_article_qualification.fields.no_open_tasks',
                'required' => false,
            ])
            ->add('noPlan', CheckboxType::class, [
                'label' => 'first_article_qualification.fields.no_plan',
                'required' => false,
            ])
            ->add('completedAt-after', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.completedAt.after',
                'property_path' => '[completedAt][after]',
                'required' => false,
            ])
            ->add('productFamily', ProductFamilyChoiceType::class, [
                'label' => 'catalogue.family.product_family',
                'translation_domain' => 'catalogue',
                'required' => false,
            ])
            ->add('xls', SubmitType::class, [
                'label' => 'button.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-capitalize'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'first_article_qualification',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_faq_filters';
    }
}
