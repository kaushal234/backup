<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExpertiseLevelTransferType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('target', ExpertiseLevelChoiceType::class, [
            'label' => 'expertise_level.transfer.choice',
            'required' => false,
            'multiple' => false,
        ])
        ->add('delete', CheckboxType::class, [
            'label' => 'expertise_level.transfer.delete',
            'required' => false,
        ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['translation_domain' => 'supplier_ranking']);
    }
}
