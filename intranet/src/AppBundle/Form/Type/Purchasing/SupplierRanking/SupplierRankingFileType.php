<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierRankingFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', FileCategoryChoiceType::class, [
                'label' => 'fields.category',
                'required' => true,
                'multiple' => false,
            ])
            ->add('file', FileType::class, [
                'translation_domain' => 'file_type',
                'label' => 'file_type.file_upload',
            ])
            ->add('expiredAt', DatePickerType::class, [
                'required' => false,
                'label' => 'fields.expired_at',
            ])
            ->add('supplierRanking', HiddenType::class, [
                'property_path' => '[supplierRanking][id]',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'supplier_ranking',
        ]);
    }
}
