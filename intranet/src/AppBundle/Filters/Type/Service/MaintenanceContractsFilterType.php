<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Service;

use AppBundle\Form\Type\Sales\Customer\CustomerChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MaintenanceContractsFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('buyer', CustomerChoiceType::class, [
                'label' => 'service.equipment_record.fields.buyer',
                'required' => false,
            ])
            ->add('endUser', CustomerChoiceType::class, [
                'label' => 'service.equipment_record.fields.end_user',
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'label' => 'fields.description',
                'translation_domain' => 'messages',
                'required' => false,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'service',
            'csrf_protection' => false,
            'allow_extra_fields' => false,
        ]);
    }

    public function getName(): string
    {
        return 'app_service_maintenance_contracts_filters';
    }
}
