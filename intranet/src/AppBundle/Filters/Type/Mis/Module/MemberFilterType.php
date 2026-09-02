<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Mis\Module;

use ApiBundle\Form\Type\ResourceCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as CoreSearchType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', CoreSearchType::class, [
                'label' => 'mis.member.filter.user',
                'required' => false,
            ])
            ->add('businessUnit', ResourceCollectionType::class, [
                'label' => 'mis.member.fields.business_unit',
                'resource' => 'business_units',
                'property' => 'name',
                'placeholder' => '',
                'required' => false,
            ])
            ->add('position', ResourceCollectionType::class, [
                'label' => 'position',
                'resource' => 'positions',
                'property' => 'description',
                'placeholder' => '',
                'required' => false,
            ])
            ->add('admin', CheckboxType::class, [
                'label' => 'mis.member.filter.admin',
                'required' => false,
            ])
            ->add('filter', SubmitType::class, [
                'label' => 'button.filter',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
            'csrf_protection' => false,
        ]);
    }
}
