<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use ApiBundle\Form\Type\ResourceCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UpdateTaskType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('thirdPartyApp', ResourceCollectionType::class, [
                'label' => 'mis.update_task.fields.third_party_app',
                'resource' => 'third_party_apps',
                'property' => 'name',
            ])
            ->add('businessUnit', ResourceCollectionType::class, [
                'label' => 'mis.update_task.fields.business_unit',
                'resource' => 'business_units',
                'property' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
        ]);
    }
}
