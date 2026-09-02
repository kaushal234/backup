<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DerogationEditType extends AbstractType
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('assignee', PeopleAutocompleteChoiceType::class, [
                'label' => 'tasks.assignee',
                'help' => $this->translator->trans('crab.derogation.help_assignee', [], 'crab'),
                'data_disabled' => [$options['assignee']],
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'fields.comment',
                'required' => true,
                'attr' => ['maxlength' => 500, 'style' => 'resize:vertical; height:200px'],
            ])
            ->add('file', FileType::class, [
                'translation_domain' => 'file_type',
                'required' => false,
                'label' => 'file_type.file_upload',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'messages',
            'assignee' => null,
            'file' => null,
        ]);
    }
}
