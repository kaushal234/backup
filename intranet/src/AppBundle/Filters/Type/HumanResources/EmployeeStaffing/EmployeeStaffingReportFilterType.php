<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\HumanResources\EmployeeStaffing;

use AppBundle\Form\Type\Directory\EntityChoiceType;
use AppBundle\Form\Type\HumanResources\EmployeeStaffingSnapshotChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EmployeeStaffingReportFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $snapshotOptions = [
            'label' => false,
            'required' => false,
        ];

        $builder
            ->add('entity', EntityChoiceType::class, [
                'label' => false,
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'button.submit',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('submitRegionalResultsReview', SubmitType::class, [
                'label' => 'RRR',
                'attr' => ['class' => 'btn btn-info'],
            ])
            ->add('snapshot', EmployeeStaffingSnapshotChoiceType::class, $snapshotOptions)
            ->add('categorized', HiddenType::class)
            ->add('contractType', HiddenType::class)
            ->add('positionCategory', HiddenType::class)
        ;

        $builder->addEventListener(
            FormEvents::PRE_SUBMIT,
            static function (FormEvent $event) use ($snapshotOptions) {
                $form = $event->getForm();
                $data = $event->getData();

                $snapshotOptions['entity'] = $data['entity'];

                $form->remove('snapshot');
                $form->add('snapshot', EmployeeStaffingSnapshotChoiceType::class, $snapshotOptions);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'method' => Request::METHOD_GET,
        ]);
    }
}
