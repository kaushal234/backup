<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Task;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class TaskCommentType extends AbstractType
{
    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $builder->getData();

        $commentLabel = match (true) {
            $options['task_close'] => 'task.fields.closing_reason',
            $options['task_transfer'] => 'task.fields.transfer_reason',
            $options['task_reschedule'] => 'task.fields.task_reschedule',
            $options['task_pause'] => 'task.fields.task_pause',
            default => 'task.fields.comment',
        };

        $nextLabel = match (true) {
            $options['task_close'] => 'task.fields.closing_next',
            $options['task_transfer'] => 'task.fields.transfer_next',
            $options['task_reschedule'] => 'task.fields.reschedule_next',
            $options['task_pause'] => 'task.fields.pause_next',
            default => 'task.fields.comment_next',
        };

        if ($options['task_transfer']) {
            $builder
                ->add('assignee', PeopleAutocompleteChoiceType::class, [
                    'label' => 'task.fields.assignee',
                    'translation_domain' => 'task',
                    'uri' => 'people',
                    'row_attr' => ['class' => 'm-0'],
                    'data' => null,
                ])
            ;
        }

        if ($options['task_transfer'] || $options['task_reschedule']) {
            $builder->add('rescheduleDate', DatePickerType::class, [
                'restrictions' => [
                    'minDateStr' => 'now',
                ],
                'label' => 'task.fields.reschedule_date',
                'data' => (new \DateTime('+ 1days'))->format('c'),
                'row_attr' => ['class' => 'm-0'],
            ]);
        }

        $builder
            ->add('comment', CKEditorType::class, [
                'constraints' => [new NotBlank()],
                'label' => $commentLabel,
                'required' => true,
                'config_name' => 'minimal_toolbar',
                'row_attr' => ['class' => 'm-0'],
                'mapped' => false,
            ])
            ->add('file', FileType::class, [
                'label' => 'task.fields.upload_file',
                'required' => false,
                'row_attr' => ['class' => 'm-0'],
                'mapped' => false,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-info m-0 py-1 px-0 w-100'],
            ])
            ->add('recipients', PeopleAutocompleteChoiceType::class, [
                'label' => 'task.fields.ccs',
                'multiple' => true,
                'required' => false,
                'row_attr' => ['class' => 'm-0'],
            ])
        ;

        $taskInSession = false;
        foreach (($this->requestStack->getSession()->get('tasks') ?? []) as $task) {
            if ($task['id'] === $data['id']) {
                $taskInSession = true;
            }
        }

        if ($taskInSession && \count($this->requestStack->getSession()->get('tasks')) > 1) {
            $builder->add('nextTask', SubmitType::class, [
                'label' => $nextLabel,
                'attr' => ['class' => 'btn btn-info m-0 py-1 px-2 w-100'],
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'task',
            'task_close' => false,
            'task_transfer' => false,
            'task_reschedule' => false,
            'task_pause' => false,
        ]);
    }
}
