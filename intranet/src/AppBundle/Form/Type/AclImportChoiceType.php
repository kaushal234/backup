<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use AppBundle\Form\Type\Directory\Location\LocationChoiceType;
use AppBundle\Form\Type\Directory\People\AclChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclImportChoiceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('user', PeopleAutocompleteChoiceType::class, [
                'label' => 'directory.user.name',
                'placeholder' => 'directory.user.select',
                'translation_domain' => 'directory',
            ])
            ->add('location', LocationChoiceType::class, [
                'label' => 'directory.import_acl.location',
                'required' => false,
                'placeholder' => 'directory.location.select',
                'translation_domain' => 'directory',
                'label_format' => '',
            ])
            ->add('keep_location', CheckboxType::class, [
                'label' => 'directory.import_acl.keep_acls',
                'required' => false,
                'translation_domain' => 'directory',
            ])
            ->add('submit_step_1', SubmitType::class, [
                'label' => 'directory.import_acl.select_acls', // get ACLs
                'translation_domain' => 'directory',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, static function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            $form->add('acl_choice', AclChoiceType::class, [
                'label' => false,
                'user' => $data['user'],
                'location' => $data['location'] ?? null,
            ]);

            $form->add('submit_step_2', SubmitType::class, [
                'label' => 'menu.import',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ]);
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefined(['user']);
        $resolver->setDefined(['location']);
        $resolver->setDefault('allow_extra_fields', true);
    }

    public function getBlockPrefix(): string
    {
        return 'app_bundle_acl_import_choice_type';
    }
}
