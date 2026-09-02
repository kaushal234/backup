<?php

declare(strict_types=1);

namespace App\Form\VendorWarrantyClaim;

use App\DataTransferObject\VendorWarrantyClaim\AddSupplierCorrectiveActionRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierCorrectiveActionRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('issueOrigin', TextareaType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('correctiveAction', TextareaType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('shortDescription', TextareaType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('comment', TextareaType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('fileDescription', TextareaType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AddSupplierCorrectiveActionRequest::class,
            'csrf_protection' => true,
        ]);
    }
}
