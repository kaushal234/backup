<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\TextAreaEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class PartNumberTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('partNumber', PartsNumberAutocompleteChoiceType::class, [
                'id_key' => '[itemCode]',
                'js_template_selection' => '{{ itemCode }} {{ description }}',
                'label' => 'fields.part_number',
                'translation_domain' => 'messages',
            ])
            ->add('location', LocationChoiceType::class, [
                'label' => 'directory.department.fields.factory',
                'translation_domain' => 'directory',
                'erp_in_label' => true,
                'filters' => ['has_any_capability' => ['warehouse', 'factory']],
            ])
            ->add('assignee', PeopleAutocompleteChoiceType::class, [
                'required' => true,
                'label' => 'tasks.assignee',
            ])
            ->add('shortDescription', TextType::class, [
                'required' => true,
                'label' => 'display.table.scar.headers.short_description',
                'constraints' => [
                    new NotBlank(),
                    new Length(max: 100),
                ],
                'attr' => [
                    'maxlength' => 100,
                ],
            ])
            ->add('description', TextAreaEditorType::class, [
                'label' => 'tasks.description',
                'translation_domain' => 'messages',
                'required' => true,
            ])
            ->add('dueDate', DatePickerType::class, [
                'required' => true,
                'label' => 'tasks.dueDate',
                'defaultDate' => new \DateTime(),
                'data' => (new \DateTime('+30 days'))->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'restrictions' => [
                    'minDateStr' => 'now',
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('startedAt', DatePickerType::class, [
                'required' => true,
                'label' => 'task.fields.started_date',
                'translation_domain' => 'task',
                'defaultDate' => new \DateTime(),
                'data' => (new \DateTime())->format(DatePickerType::DEFAULT_INPUT_FORMAT),
                'restrictions' => [
                    'minDateStr' => 'now',
                ],
            ])
            ->add('indiceFactor', ChoiceType::class, [
                'label' => 'task.fields.indice_factor',
                'translation_domain' => 'task',
                'required' => true,
                'choices' => [
                    'IF 1' => 'IF 1',
                    'IF 10' => 'IF 10',
                    'IF 100' => 'IF 100',
                    'IF 1000' => 'IF 1000',
                    'IF 10000' => 'IF 10000',
                ],
                'data' => 'IF 1',
            ])
            ->add('recipients', PeopleAutocompleteChoiceType::class, [
                'label' => 'task.fields.ccs',
                'translation_domain' => 'task',
                'multiple' => true,
                'required' => false,
                'row_attr' => ['class' => 'm-0'],
            ])
        ;
        if (!$options['is_edit']) {
            $builder->add('file', FileType::class, [
                'label' => 'menu.file',
                'required' => false,
                'mapped' => false,
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
            'is_edit' => false,
        ]);
    }
}
