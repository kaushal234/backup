<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Mis;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\Division\DivisionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Division\SubDivisionChoiceType;
use AppBundle\Form\Type\Directory\Location\LocationAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Location\RegionAutocompleteChoiceType;
use AppBundle\Form\Type\ImportanceFactorType;
use AppBundle\Form\Type\Mis\Application\ApplicationChoiceType;
use AppBundle\Form\Type\Mis\Module\ModuleChoiceType;
use AppBundle\Form\Type\Mis\SupportTeam\SupportTeamChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TroubleTicketAuditFilterType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationAutocompleteChoiceType::class, [
                'property_path' => '[createdBy.businessUnit.location]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('region', RegionAutocompleteChoiceType::class, [
                'property_path' => '[createdBy.businessUnit.region]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('subDivision', SubDivisionChoiceType::class, [
                'property_path' => '[createdBy.businessUnit.region.subDivision]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('division', DivisionAutocompleteChoiceType::class, [
                'property_path' => '[createdBy.businessUnit.region.subDivision.division]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('createdAfter', DatePickerType::class, [
                'label' => 'sales_forecasts.fields.created_after',
                'property_path' => '[createdAt][after]',
                'translation_domain' => 'sales_forecasts',
                'required' => false,
            ])
            ->add('createdBefore', DatePickerType::class, [
                'label' => 'spq.form.created_at_before',
                'property_path' => '[createdAt][before]',
                'translation_domain' => 'spq',
                'required' => false,
            ])
            ->add('application', ApplicationChoiceType::class, [
                'property_path' => '[module.application]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'trouble_ticket.fields.type',
                'required' => false,
                'translation_domain' => 'trouble_ticket',
                'choices' => ['Incident' => 'Incident', 'Request' => 'Request'],
                'property_path' => '[type.type]',
            ])
            ->add('module', ModuleChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('supportTeam', SupportTeamChoiceType::class, [
                'property_path' => '[createdBy.premise.supportTeam]',
                'required' => false,
                'multiple' => true,
            ])
            ->add('indiceFactor', ImportanceFactorType::class, [
                'required' => false,
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}
