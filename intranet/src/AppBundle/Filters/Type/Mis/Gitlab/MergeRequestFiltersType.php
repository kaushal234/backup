<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Mis\Gitlab;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\DEVPeopleChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MergeRequestFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('author', DEVPeopleChoiceType::class, [
                'label' => 'mis.changelog.fields.author',
                'placeholder' => 'mis.modules.make_selection',
                'required' => false,
            ])
            ->add('merged_after', DatePickerType::class, [
                'label' => 'merged after',
                'required' => false,
            ])
            ->add('merged_before', DatePickerType::class, [
                'label' => 'merged before',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
