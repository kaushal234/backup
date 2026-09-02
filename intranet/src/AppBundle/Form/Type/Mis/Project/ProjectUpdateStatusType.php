<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Project;

use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectUpdateStatusType extends AbstractType
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Add phase information on each available status except CANCELLED, CLOSED and PENDING
        $statusKeyWithDescription = array_map(fn ($status) => ('CANCELLED' !== $status && 'CLOSED' !== $status && 'PENDING' !== $status)
            ? $status.' - '.$this->translator->trans(\sprintf('mis_project.fields.phase_description_%s', mb_substr($status, -1)), [], 'mis_project')
            : $status, $options['availableStatus']);

        // Create the choice array [label => value]
        $statuses = array_combine($statusKeyWithDescription, $options['availableStatus']);

        // Revert to get the next phase in first
        krsort($statuses);

        $builder
            ->add('status', ChoiceType::class, [
                'label' => 'mis_project.fields.status',
                'choices' => $statuses,
            ])
            ->add('comment', CKEditorType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'mis_project.fields.comment',
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'label' => 'mis_project.fields.upload_file',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn btn-info m-0'],
            ])
        ;

        if ($options['comment']) {
            $builder->remove('status');
        }

        if ($options['close']) {
            $builder
                ->remove('comment')
                ->remove('status')
            ;

            $builder->add('conclusion', CKEditorType::class, [
                'constraints' => [new NotBlank()],
                'label' => 'mis_project.fields.conclusion',
                'required' => true,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis_project',
            'availableStatus' => [],
            'close' => false,
            'status' => false,
            'comment' => false,
        ]);
    }
}
