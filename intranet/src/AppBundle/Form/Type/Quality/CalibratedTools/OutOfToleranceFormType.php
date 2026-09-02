<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\CalibratedTools;

use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Manager\Quality\CalibratedTools\Statuses\OutOfToleranceFormStatus;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OutOfToleranceFormType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('status', SelectFormType::class, [
                'label' => 'out_of_tolerance_form.fields.status',
                'choices' => $options['available_statuses'],
                'required' => true,
            ])
            ->add('impactAnalysis', CKEditorType::class, [
                'label' => 'out_of_tolerance_form.fields.impactAnalysis',
                'config_name' => 'simple',
            ])
            ->add('correctiveMeasures', CKEditorType::class, [
                'label' => 'out_of_tolerance_form.fields.correctiveMeasures',
                'config_name' => 'simple',
            ])
            ->add('analysisBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'out_of_tolerance_form.fields.analysisBy',
                'required' => true,
            ])
            ->add('files', FileType::class, [
                'label' => 'out_of_tolerance_form.fields.files',
                'multiple' => true,
                'required' => false,
                'data_class' => null,
            ])

        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'out_of_tolerance_form',
            'no_status' => true,
            'available_statuses' => OutOfToleranceFormStatus::getStatuses(),
        ]);
    }

    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        if ($options['no_status']) {
            $form->remove('status');
        } elseif (empty($form->get('status')->getData())) {
            $form->get('status')->setData(OutOfToleranceFormStatus::IN_PROGRESS);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_otf';
    }
}
