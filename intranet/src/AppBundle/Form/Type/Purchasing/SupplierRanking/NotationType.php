<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NotationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('notation', IntegerType::class, [
            'required' => false,
        ]);
        $builder->add('criteria', HiddenType::class, [
            'property_path' => '[criteria][@id]',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'supplier_ranking',
        ]);
    }
}
