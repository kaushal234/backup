<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service;

use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\IndiceFactorType;
use AppBundle\Form\Type\Sales\Catalogue\ProductChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeChoiceType;
use AppBundle\Form\Type\Support\EmissionRatingChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class TechnicianOnCallSettingType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('factory', FactoryChoiceType::class, [
                'property_path' => '[equipmentRecord.manufacturerLocation]',
                'label' => 'toc.filters.location',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'translation_domain' => 'technician_on_call',
                'attr' => ['class' => 'chosen-select'],
            ])
            ->add('product', ProductChoiceType::class, [
                'property_path' => '[equipmentRecord.product]',
                'label' => 'toc.fields.product',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'translation_domain' => 'technician_on_call',
                'attr' => ['class' => 'chosen-select'],
            ])
            ->add('indiceFactor', IndiceFactorType::class, [
                'required' => false,
                'multiple' => true,
                'attr' => ['class' => 'chosen-select'],
            ])
            ->add('erType', ProductTypeChoiceType::class, [
                'property_path' => '[equipmentRecord.product.family.productType]',
                'label' => 'toc.fields.er_type',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'translation_domain' => 'technician_on_call',
                'attr' => ['class' => 'chosen-select'],
            ])
            ->add('emissionRating', EmissionRatingChoiceType::class, [
                'property_path' => '[equipmentRecord.emissionRating]',
                'label' => 'toc.fields.emission_rating',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'translation_domain' => 'technician_on_call',
                'attr' => ['class' => 'chosen-select'],
            ])
            ->add('airport', AirportChoiceType::class, [
                'label' => 'toc.fields.airport',
                'required' => false,
                'multiple' => true,
                'expanded' => false,
                'translation_domain' => 'technician_on_call',
                'attr' => ['class' => 'chosen-select'],
            ])
        ;
    }
}
