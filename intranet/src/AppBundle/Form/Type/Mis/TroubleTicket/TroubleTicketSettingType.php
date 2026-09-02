<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\Division\DivisionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Division\SubDivisionChoiceType;
use AppBundle\Form\Type\Directory\Location\LocationAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Location\RegionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Premise\PremiseChoiceType;
use AppBundle\Form\Type\ImportanceFactorType;
use AppBundle\Form\Type\Mis\Application\ApplicationChoiceType;
use AppBundle\Form\Type\Mis\Module\ModuleChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TroubleTicketSettingType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('location', LocationAutocompleteChoiceType::class, [
                'property_path' => '[createdBy.businessUnit.location]',
                'label' => 'menu.location.title',
                'required' => false,
                'multiple' => true,
                'translation_domain' => 'messages',
            ])
            ->add('region', RegionAutocompleteChoiceType::class, [
                'property_path' => '[createdBy.businessUnit.region]',
                'label' => 'menu.region.title',
                'required' => false,
                'multiple' => true,
                'translation_domain' => 'messages',
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
            ->add('status', SelectFormType::class, [
                'label' => 'customers.fields.status',
                'required' => false,
                'choices' => [
                    'PENDING' => 'PENDING',
                    'PENDING MOO/GKU' => 'PENDING MOO/GKU',
                    'IN PROGRESS' => 'IN PROGRESS',
                    'AWAITING USER' => 'AWAITING USER',
                    'MOO/GKU AWAITING USER' => 'MOO/GKU AWAITING USER',
                    'SOLUTION PROPOSED' => 'SOLUTION PROPOSED',
                    'MOO/GKU SOLUTION PROPOSED' => 'MOO/GKU SOLUTION PROPOSED',
                    'SOLVED' => 'SOLVED',
                    'ALREADY RAISED' => 'ALREADY RAISED',
                    'NOT AN ISSUE' => 'NOT AN ISSUE',
                    'NOT APPROVED' => 'NOT APPROVED',
                ],
                'translation_domain' => 'sales_customers',
                'multiple' => true,
            ])
            ->add('application', ApplicationChoiceType::class, [
                'label' => 'trouble_ticket.fields.application',
                'property_path' => '[module.application]',
                'required' => false,
                'translation_domain' => 'trouble_ticket',
                'multiple' => true,
            ])
            ->add('type', TypeChoiceType::class, [
                'label' => 'trouble_ticket.fields.type',
                'required' => false,
                'translation_domain' => 'trouble_ticket',
                'multiple' => true,
            ])
            ->add('module', ModuleChoiceType::class, [
                'required' => false,

                'multiple' => true,
            ])
            ->add('premise', PremiseChoiceType::class, [
                'label' => 'directory.premise.title',
                'property_path' => '[createdBy.premise]',
                'required' => false,
                'translation_domain' => 'directory',
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
            'empty_data' => [],
        ]);
    }
}
