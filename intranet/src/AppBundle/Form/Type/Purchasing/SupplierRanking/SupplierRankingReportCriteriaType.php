<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class SupplierRankingReportCriteriaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('expertiseLevel', ExpertiseLevelChoiceType::class, [
                'label' => 'fields.expertise_level',
                'translation_domain' => 'supplier_ranking',
                'required' => false,
                'multiple' => true,
            ])
            ->add('classification', ClassificationChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('location', LocationChoiceType::class, [
                'label' => 'fields.location',
                'translation_domain' => 'supplier_ranking',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }
}
