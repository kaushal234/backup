<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Mis;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\DEVPeopleChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Mis\Module\ModuleChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeLogFiltersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('module', ModuleChoiceType::class, [
                'label' => 'mis.changelog.fields.module',
                'placeholder' => 'mis.modules.make_selection',
                'required' => false,
            ])
            ->add('moo', PeopleAutocompleteChoiceType::class, [
                'label' => 'mis.modules.fields.moo',
                'placeholder' => 'mis.modules.make_selection',
                'required' => false,
            ])
            ->add('author', DEVPeopleChoiceType::class, [
                'label' => 'mis.changelog.fields.author',
                'placeholder' => 'mis.modules.make_selection',
                'required' => false,
            ])
            ->add('message', TextType::class, [
                'label' => 'mis.changelog.fields.description',
                'required' => false,
            ])
            ->add('ticket', NumberType::class, [
                'label' => 'mis.changelog.fields.ticket',
                'required' => false,
            ])
            ->add('createdAfter', DatePickerType::class, [
                'label' => 'sales_forecasts.fields.created_after',
                'property_path' => '[date][after]',
                'translation_domain' => 'sales_forecasts',
                'required' => false,
            ])
            ->add('createdBefore', DatePickerType::class, [
                'label' => 'spq.form.created_at_before',
                'property_path' => '[date][before]',
                'translation_domain' => 'spq',
                'required' => false,
            ])
            ->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_changelog';
    }
}
