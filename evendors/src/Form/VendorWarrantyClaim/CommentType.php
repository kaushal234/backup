<?php

declare(strict_types=1);

namespace App\Form\VendorWarrantyClaim;

use App\DataTransferObject\VendorWarrantyClaim\AddVendorWarrantyClaimComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'required' => true,
                'attr' => ['class' => 'form-control'],
            ])
            ->add('file', FileType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => AddVendorWarrantyClaimComment::class,
            'csrf_protection' => true,
        ]);
    }
}
