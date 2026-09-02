<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\HumanResources;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class EmployeeStaffingDownloadFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('payload', HiddenType::class, [
                'mapped' => false,
                'data' => json_encode($options['payload'] ?? [], \JSON_THROW_ON_ERROR),
            ])
            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'translation_domain' => 'messages',
                'attr' => [
                    'class' => 'btn btn-info',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => 'POST',
            'payload' => [],
        ]);

        $resolver->setAllowedTypes('payload', 'array');
    }
}
