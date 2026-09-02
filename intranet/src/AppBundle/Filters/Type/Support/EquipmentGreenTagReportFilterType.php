<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Support;

use AppBundle\Form\Type\Common\MonthPickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductFamilyChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class EquipmentGreenTagReportFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('manufacturerLocation', FactoryChoiceType::class, [
                'label' => 'er.fields.location',
                'translation_domain' => 'customer_service_record',
                'required' => true,
            ])
            ->add('day', MonthPickerType::class, [
                'label' => 'support.green_tag_report.by_month',
                'translation_domain' => 'support',
                'required' => true,
            ])
            ->add('family', ProductFamilyChoiceType::class, [
                'property_path' => '[equipmentRecords.product.family]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary text-uppercase'],
            ])
        ;
    }
}
