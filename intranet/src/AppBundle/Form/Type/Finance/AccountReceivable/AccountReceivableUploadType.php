<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Finance\AccountReceivable;

use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountReceivableUploadType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
            ])

            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        if ($options['upload_file']) {
            $builder->add('file', FileType::class, [
                'translation_domain' => 'file_type',
                'label' => 'file_type.file_upload',
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'upload_file' => false,
        ]);
    }
}
