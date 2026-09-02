<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Directory;

use ApiBundle\Form\Type\ResourceCollectionType;
use AppBundle\Form\Type\Common\DatePickerType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DirectoryPeopleDownloadFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('businessUnit', ResourceCollectionType::class, [
                'label' => 'directory.location.fields.businessUnit',
                'required' => false,
                'resource' => 'business_units',
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'directory.location.make_selection',
            ])
            ->add('hidden', CheckboxType::class, [
                'label' => 'directory.people.search.hidden',
                'required' => false,
            ])
            ->add('createdBefore', DatePickerType::class, [
                'label' => 'directory.people.search.created_before',
                'widget' => 'single_text',
                'property_path' => '[createdAt][before]',
                'required' => false,
            ])
            ->add('createdAfter', DatePickerType::class, [
                'label' => 'directory.people.search.created_after',
                'property_path' => '[createdAt][after]',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('disabledBefore', DatePickerType::class, [
                'label' => 'directory.people.search.disabled_before',
                'widget' => 'single_text',
                'property_path' => '[disabledAt][before]',
                'required' => false,
            ])
            ->add('disabledAfter', DatePickerType::class, [
                'label' => 'directory.people.search.disabled_after',
                'property_path' => '[disabledAt][after]',
                'widget' => 'single_text',
                'required' => false,
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
            'translation_domain' => 'directory',
        ]);
    }
}
