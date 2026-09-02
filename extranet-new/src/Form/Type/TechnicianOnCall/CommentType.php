<?php

declare(strict_types=1);

namespace App\Form\Type\TechnicianOnCall;

use App\DataTransferObject\TechnicianOnCall\AddTechnicianOnCallComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('file', FileType::class, [
                'attr' => [
                    'class' => 'd-none',
                    'data-file-input-target' => 'input',
                    'data-action' => 'change->file-input#update',
                ],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AddTechnicianOnCallComment::class,
            'csrf_protection' => true,
        ]);
    }
}
