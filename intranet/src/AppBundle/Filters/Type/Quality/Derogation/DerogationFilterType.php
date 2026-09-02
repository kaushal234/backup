<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality\Derogation;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DerogationFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('assignee', PeopleAutocompleteChoiceType::class, [
                'label' => 'tasks.assignee',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('assignor', PeopleAutocompleteChoiceType::class, [
                'label' => 'finance.approver.assignor',
                'translation_domain' => 'finance',
                'required' => false,
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'trouble_ticket.fields.short_description',
                'translation_domain' => 'trouble_ticket',
                'required' => false,
            ])
            ->add('dueDateAfter', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.due_date.after',
                'property_path' => '[dueDate][after]',
                'translation_domain' => 'first_article_qualification',
                'required' => false,
            ])
            ->add('dueDateBefore', DatePickerType::class, [
                'label' => 'first_article_qualification.fields.due_date.before',
                'property_path' => '[dueDate][before]',
                'translation_domain' => 'first_article_qualification',
                'required' => false,
            ])
            ->add('status', SelectFormType::class, [
                'label' => 'customers.fields.status',
                'required' => false,
                'choices' => [
                    'OPEN' => 'OPEN',
                    'DENIED' => 'DENIED',
                    'ACCEPTED' => 'ACCEPTED',
                    'ARCHIVED' => 'ARCHIVED',
                ],
                'translation_domain' => 'sales_customers',
                'multiple' => true,
            ])
            ->add('equipmentRecord', EquipmentRecordAutocompleteChoiceType::class, [
                'property_path' => '[crabs.equipmentRecord]',
                'label' => 'csr.title.er',
                'translation_domain' => 'customer_service_record',
                'required' => false,
            ])
            ->add('model', ProductAutocompleteChoiceType::class, [
                'property_path' => '[crabs.equipmentRecord.product]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
