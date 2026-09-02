<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Legal;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class ContractAiAnalyzeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'picture',
                'translation_domain' => 'engineering_pictogram',
                'required' => true,
                'constraints' => [
                    new Assert\File(
                        maxSize: '32M',
                        mimeTypes: ['application/pdf'],
                        maxSizeMessage: 'files.max_size',
                        mimeTypesMessage: 'files.mime_type',
                    ),
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'csrf_protection' => false,
        ]);
    }
}
