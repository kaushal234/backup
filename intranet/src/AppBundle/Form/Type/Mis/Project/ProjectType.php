<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Project;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\SwitchType;
use AppBundle\Form\Type\Directory\Location\RegionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Mis\Module\ModuleChoiceType;
use AppBundle\Form\Type\TextAreaEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $data = $builder->getData();
        $isNew = !isset($data['@id']);
        $activePhase = $data['activePhase']['number'] ?? null;

        $builder
            ->add('confidential', SwitchType::class, [
                'label' => 'mis_project.fields.confidential',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'mis_project.fields.name',
            ])
            ->add('teamsLink', TextType::class, [
                'required' => false,
                'label' => 'mis_project.fields.teams_link',
            ])
            ->add('tags', TagChoiceType::class, [
                'label' => 'mis_project.fields.tags',
                'multiple' => true,
            ])
            ->add('region', RegionAutocompleteChoiceType::class, [
                'label' => 'mis_project.fields.region',
            ])
            ->add('indicesFactor', IndiceFactorType::class, [
                'label' => 'mis_project.fields.indices_factor',
            ])
            ->add('description', TextAreaEditorType::class, [
                'label' => 'mis_project.fields.description',
            ])
            ->add('module', ModuleChoiceType::class, [
                'label' => 'mis_project.fields.module',
            ])
            ->add('projectManager', PeopleAutocompleteChoiceType::class, [
                'label' => 'mis_project.fields.project_manager',
            ])
            ->add('misOwner', PeopleAutocompleteChoiceType::class, [
                'label' => 'mis_project.fields.mis_owner',
            ])
            ->add('startedAt', DatePickerType::class, [
                'label' => 'mis_project.fields.started_at',
                'input_format' => 'd.m.Y',
            ])
            ->add('phases', CollectionType::class, [
                'entry_type' => ProjectPhaseType::class,
                'prototype' => true,
                'entry_options' => [
                    'is_new' => $isNew,
                    'active_phase' => $activePhase,
                ],
            ])
            ->add('moduleKeyUsers', PeopleAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'mis_project.fields.module_key_users',
                'multiple' => true,
            ])
            ->add('misMembers', PeopleAutocompleteChoiceType::class, [
                'required' => false,
                'label' => 'mis_project.fields.mis_members',
                'multiple' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis_project',
        ]);
    }
}
