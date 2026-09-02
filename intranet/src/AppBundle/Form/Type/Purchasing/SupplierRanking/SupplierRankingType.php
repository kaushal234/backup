<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Purchasing\SupplierRanking;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Service\DataProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierRankingType extends AbstractType
{
    protected DataProvider $dataProvider;

    public function __construct(DataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $supplierRanking = $builder->getData();

        $classifications[$supplierRanking['classification']['name']] = $supplierRanking['classification']['@id'];
        foreach ($supplierRanking['classification']['targetClassifications'] as $targetClassification) {
            $classifications[$targetClassification['name']] = $targetClassification['@id'];
        }
        $supplierRanking['classification'] = $supplierRanking['classification']['@id'];

        $builder->setData($supplierRanking);

        $builder
            ->add('expertiseLevel', ExpertiseLevelChoiceType::class, [
                'label' => 'fields.expertise_level',
                'required' => true,
                'multiple' => false,
            ])
            ->add('classification', ClassificationChoiceType::class, [
                'label' => 'fields.classification',
                'required' => true,
                'multiple' => false,
                'choices' => $classifications,
            ])
            ->add('lastScreeningAt', DatePickerType::class, [
                'label' => 'fields.last_screening_at',
                'required' => false,
            ])
            ->add('notations', CollectionType::class, [
                'entry_type' => NotationType::class,
                'allow_add' => true,
                'allow_delete' => false,
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
