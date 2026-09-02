<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality\CalibratedTools;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\CalibratedTools\LocationAreaChoiceType;
use AppBundle\Form\Type\Quality\CalibratedTools\ToolTypeChoiceType;
use AppBundle\Form\Type\Quality\Custom\ItemsPerPageType;
use AppBundle\Manager\Quality\CalibratedTools\Statuses\OutOfToleranceFormStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutOfToleranceFormFilters extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', SelectFormType::class, [
                'label' => 'out_of_tolerance_form.fields.status',
                'choices' => OutOfToleranceFormStatus::getStatuses(),
                'required' => false,
            ])
            ->add('impactAnalysis', TextType::class, [
                'label' => 'out_of_tolerance_form.fields.impactAnalysis',
                'required' => false,
            ])
            ->add('correctiveMeasures', TextType::class, [
                'label' => 'out_of_tolerance_form.fields.correctiveMeasures',
                'required' => false,
            ])
            ->add('analysisBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'out_of_tolerance_form.fields.analysisBy',
                'required' => false,
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'out_of_tolerance_form.filters.factory',
                'translation_domain' => 'out_of_tolerance_form',
                'property_path' => '[calibrationLog.tool.locationArea.factory]',
                'required' => false,
            ])
            ->add('toolType', ToolTypeChoiceType::class, [
                'label' => 'out_of_tolerance_form.filters.tool_type',
                'property_path' => '[calibrationLog.tool.toolType]',
                'required' => false,
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'out_of_tolerance_form.filters.serialNumber',
                'property_path' => '[calibrationLog.tool.serialNumber]',
                'required' => false,
            ])
            ->add('locationArea', LocationAreaChoiceType::class, [
                'label' => 'out_of_tolerance_form.filters.locationArea',
                'property_path' => '[calibrationLog.tool.locationArea]',
                'required' => false,
            ])
            ->add('itemsPerPage', ItemsPerPageType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'out_of_tolerance_form',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}
