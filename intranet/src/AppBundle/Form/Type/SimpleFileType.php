<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SimpleFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'file_type.file_upload',
            ])
            ->add('description', TextType::class, [
                'required' => $options['description_required'],
                'label' => 'file_type.file_description',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
        if ($options['display_public']) {
            $builder->add('public', CheckboxType::class, [
                'required' => false,
                'label' => 'fields.file_is_public',
                'help' => 'visibility.checkbox',
            ]);
        }
        if ($options['only_file']) {
            $builder->remove('description');
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'file_type',
            'allow_extra_fields' => true,
            'display_public' => false,
            'only_file' => false,
            'description_required' => false,
        ]);
    }
}
