<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\TroubleTicket;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\Location\RegionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\MISAutocompleteChoiceType;
use AppBundle\Form\Type\Mis\Application\ApplicationChoiceType;
use AppBundle\Form\Type\Mis\Module\ModuleChoiceType;
use AppBundle\Form\Type\Mis\SupportTeam\SupportTeamChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MISReportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('misAssignee', MISAutocompleteChoiceType::class, [
                'label' => 'trouble_ticket.fields.mis_assignee',
                'required' => false,
                'multiple' => true,
            ])
            ->add('application', ApplicationChoiceType::class, [
                'label' => 'trouble_ticket.fields.application',
                'required' => false,
                'multiple' => true,
            ])
            ->add('module', ModuleChoiceType::class, [
                'label' => 'trouble_ticket.fields.module',
                'required' => false,
                'multiple' => true,
            ])
            ->add('region', RegionAutocompleteChoiceType::class, [
                'label' => 'sidebar.hr.directory.region',
                'translation_domain' => 'sidebar',
                'required' => false,
                'multiple' => true,
            ])
            ->add('type', SelectFormType::class, [
                'label' => 'trouble_ticket.fields.type',
                'required' => false,
                'translation_domain' => 'trouble_ticket',
                'choices' => ['INCIDENT' => 'INCIDENT', 'REQUEST' => 'REQUEST'],
                'property_path' => '[type.type]',
                'multiple' => true,
            ])
            ->add('supportTeam', SupportTeamChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('after', DateType::class, [
                'label' => 'trouble_ticket.fields.after',
                'required' => false,
                'widget' => 'single_text',
                'property_path' => '[createdAt][after]',
                'translation_domain' => 'trouble_ticket',
            ])
            ->add('before', DateType::class, [
                'label' => 'trouble_ticket.fields.before',
                'required' => false,
                'widget' => 'single_text',
                'property_path' => '[createdAt][before]',
                'translation_domain' => 'trouble_ticket',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'trouble_ticket',
        ]);
    }
}
