<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Quality\CalibratedTools;

use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\CalibratedTools\LocationAreaChoiceType;
use AppBundle\Form\Type\Quality\CalibratedTools\ToolTypeChoiceType;
use AppBundle\Form\Type\Quality\Custom\ItemsPerPageType;
use AppBundle\Form\Type\Quality\Custom\ToolStatusChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ToolFilters extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', ToolStatusChoiceType::class, [
                'label' => 'calibration_tool.fields.status',
                'required' => false,
                'choice_translation_domain' => false,
            ])
            ->add('toolType', ToolTypeChoiceType::class, [
                'label' => 'calibration_tool.fields.tool_type',
                'required' => false,
            ])
            ->add('supervisor', PeopleAutocompleteChoiceType::class, [
                'label' => 'calibration_tool.fields.supervisor',
                'required' => false,
                'property_path' => '[locationArea.supervisor]',
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'calibration_tool.fields.factory.name',
                'required' => false,
                'property_path' => '[locationArea.factory]',
                'translation_domain' => 'calibration_tool',
                'cascading_target_form' => 'locationArea',
                'cascading_to_filter' => 'factory',
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'calibration_tool.fields.serialNumber',
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'label' => 'calibration_tool.fields.description',
                'required' => false,
            ])
            ->add('vendorId', TextType::class, [
                'label' => 'calibration_tool.fields.vendorId',
                'required' => false,
            ])
            ->add('vendorName', TextType::class, [
                'label' => 'calibration_tool.fields.vendorName',
                'required' => false,
            ])
            ->add('vendorErp', TextType::class, [
                'label' => 'calibration_tool.fields.vendorErp',
                'required' => false,
            ])
            ->add('itemsPerPage', ItemsPerPageType::class)
            ->add('locationArea', LocationAreaChoiceType::class, [
                'label' => 'calibration_tool.fields.location_area',
                'required' => false,
            ])
            ->add('csv', SubmitType::class, [
                'label' => 'button.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-capitalize'],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'calibration_tool',
            'csrf_protection' => false,
            'allow_extra_fields' => true,
        ]);
    }
}
