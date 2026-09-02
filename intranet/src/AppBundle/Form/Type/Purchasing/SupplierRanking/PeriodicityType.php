<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Range;

class PeriodicityType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('expertiseLevel', ExpertiseLevelChoiceType::class, [
                'label' => 'fields.expertise_level',
                'multiple' => false,
                'required' => true,
            ])
            ->add('classification', ClassificationChoiceType::class, [
                'label' => 'fields.classification',
                'multiple' => false,
                'required' => true,
            ])
            ->add('months', IntegerType::class, [
                'label' => 'calibration_tool.fields.calibrationIntervalUnit.months',
                'constraints' => [new Range(['min' => 0])],
                'required' => true,
                'translation_domain' => 'calibration_tool',
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
