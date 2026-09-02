<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\Crab;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class DerogationType extends AbstractType
{
    private readonly TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('description', TextareaType::class, [
                'label' => 'fields.description',
                'required' => true,
                'attr' => ['maxlength' => 500, 'style' => 'resize:vertical; height:70px'],
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'fields.short-description',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if ($options['add']) {
            $builder
                ->add('assignee', PeopleAutocompleteChoiceType::class, [
                    'label' => 'tasks.assignee',
                    'required' => false,
                    'help' => $this->translator->trans('crab.derogation.help', [], 'crab'),
                ])
                ->add('file', FileType::class, [
                    'translation_domain' => 'file_type',
                    'required' => false,
                    'label' => 'file_type.file_upload',
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'messages',
            'add' => false,
        ]);
    }
}
