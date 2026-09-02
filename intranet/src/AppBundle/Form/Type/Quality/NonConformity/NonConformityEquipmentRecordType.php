<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\NonConformity;

use AppBundle\Form\Type\Common\ReactEquipmentRecordSelectMultipleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NonConformityEquipmentRecordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $nonConformity = $builder->getData();
        $filterProducts = [];
        foreach ($nonConformity['products'] as $product) {
            $filterProducts[] = $product['@id'];
        }

        $builder
            ->add('equipmentRecords', ReactEquipmentRecordSelectMultipleType::class, [
                'label' => 'demo.fields.equipment_record',
                'translation_domain' => 'demo',
                'resource' => 'equipment_record',
                'multiple' => true,
                'required' => false,
                'filterProducts' => $filterProducts,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'non_conformity',
        ]);
    }
}
