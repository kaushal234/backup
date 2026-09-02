<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Demo;

use ApiBundle\Form\DataTransformer\IrisResourceToIdTransformer;
use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Common\DateTimePickerType;
use AppBundle\Form\Type\CountryChoiceType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\Location\SSOChoiceType;
use AppBundle\Form\Type\Directory\People\ASMAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\ASTAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\People\PSMAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Catalogue\ProductAutocompleteChoiceType;
use AppBundle\Form\Type\Sales\Customer\ApprovedCustomerAutocompleteChoiceType;
use AppBundle\Form\Type\Support\EmissionRatingChoiceType;
use AppBundle\Form\Type\Support\EquipmentRecordChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class DemoType extends AbstractType
{
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customer', ApprovedCustomerAutocompleteChoiceType::class, [
                'label' => 'demo.fields.customer',
            ])
            ->add('asm', ASMAutocompleteChoiceType::class, [
                'label' => 'demo.fields.asm',
            ])
            ->add('product', ProductAutocompleteChoiceType::class, [
                'label' => 'demo.fields.product',
            ])
            ->add('factory', FactoryChoiceType::class, [
                'label' => 'fields.factory',
                'translation_domain' => 'messages',
            ])
            ->add('sso', SSOChoiceType::class, [
                'label' => 'fields.sso',
                'translation_domain' => 'messages',
            ])
            ->add('ast', ASTAutocompleteChoiceType::class, [
                'label' => 'demo.fields.ast',
            ])
            ->add('psm', PSMAutocompleteChoiceType::class, [
                'label' => 'demo.fields.psm',
            ])
            ->add('country', CountryChoiceType::class, [
                'label' => 'demo.fields.country',
            ])
            ->add('expectedStartDate', DateTimePickerType::class, [
                'required' => false,
                'label' => 'demo.fields.expected_start_date',
            ])
            ->add('revisedEndDate', DateTimePickerType::class, [
                'required' => false,
                'label' => 'demo.fields.revised_end_date',
            ])
            ->add('emissionRating', EmissionRatingChoiceType::class, [
                'required' => true,
                'label' => 'sales_forecasts.fields.tier',
                'translation_domain' => 'sales_forecasts',
            ])
            ->add('linkAllocated', CheckboxType::class, [
                'required' => false,
                'label' => 'demo.add.link_allocated',
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'demo.submit',
                'attr' => ['class' => 'btn btn-info'],
            ])
        ;

        $builder->addModelTransformer(new IrisResourceToIdTransformer());

        if ($options['add']) {
            $builder
                ->add('comment', TextareaType::class, [
                    'required' => true,
                    'constraints' => [
                        new Assert\NotBlank(),
                    ],
                    'attr' => [
                        'style' => 'resize:vertical',
                    ],
                    'label' => 'demo.fields.justification',
                ])
                ->add('expectedClosingStatus', ChoiceType::class, [
                    'label' => 'demo.fields.expected_closing_status',
                    'required' => true,
                    'choices' => [
                        'SUCCESSFUL' => 'SUCCESSFUL',
                        'SUCCESSFUL FUTURE SALE' => 'SUCCESSFUL_FUTURE_SALE',
                        'UNSUCCESSFUL' => 'UNSUCCESSFUL',
                    ],
                ])
                ->add('expectedEndDate', DateTimePickerType::class, [
                    'required' => false,
                    'label' => 'demo.fields.expected_end_date',
                ])
            ;

            $builder->remove('revisedEndDate');
        }

        if (!$options['add']) {
            $demo = $builder->getData();

            $builder
                ->add('comment', TextareaType::class, [
                    'required' => false,
                    'attr' => [
                        'style' => 'resize:vertical',
                    ],
                    'label' => 'demo.fields.link_not_allocated_comment',
                ])
                ->add('futureDemo', DemoChoiceType::class, [
                    'required' => false,
                    'label' => 'demo.fields.future_demo',
                ])
                ->add('closingComment', TextareaType::class, [
                    'required' => false,
                    'attr' => [
                        'style' => 'resize:vertical; height:150px',
                    ],
                    'label' => 'demo.fields.closing_comment',
                ])
                ->add('status', ChoiceType::class, [
                    'label' => 'demo.fields.status',
                    'required' => false,
                    'choices' => [
                        'SUCCESSFUL' => 'SUCCESSFUL',
                        'SUCCESSFUL FUTURE SALE' => 'SUCCESSFUL_FUTURE_SALE',
                        'UNSUCCESSFUL' => 'UNSUCCESSFUL',
                        'REJECTED' => 'REJECTED',
                    ],
                ])
            ;

            if (null !== $demo['equipmentRecord'] && !\in_array($demo['status'], ['PENDING', 'SUBMITTED'], true)) {
                $builder
                    ->add('equipmentRecord', EquipmentRecordChoiceType::class, [
                        'required' => false,
                        'er_demo' => true,
                        'product' => $options['product'],
                        'label' => 'demo.fields.equipment_record',
                    ])
                ;
            }
            if (isset($demo['airport'])) {
                $builder
                    ->add('airport', AirportChoiceType::class, [
                        'required' => false,
                        'label' => 'fields.airport',
                        'translation_domain' => 'messages',
                    ])
                ;
            }

            $authorizationChecker = $this->authorizationChecker;
            $builder->addEventListener(
                FormEvents::PRE_SET_DATA,
                static function (FormEvent $event) use ($authorizationChecker, $demo) {
                    $form = $event->getForm();
                    if (!$authorizationChecker->isGranted('FEATURE_DEMO_ADMIN_EDIT') && !$authorizationChecker->isGranted('MOO_DEMO')) {
                        $form->remove('closingComment')
                             ->remove('futureDemo')
                             ->remove('status');
                    }
                    if (!\in_array($demo['status'], ['SUCCESSFUL', 'SUCCESSFUL FUTURE SALE', 'UNSUCCESSFUL', 'REJECTED'], true)) {
                        $form->remove('status');
                    }
                }
            );

            foreach (array_keys($builder->all()) as $key) {
                if (!\in_array($key, $options['authorizedFields'], true) && 'submit' !== $key) {
                    $field = $builder->get($key);
                    $field->setDisabled(true);
                }
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'demo',
            'add' => true,
            'authorizedFields' => null,
            'product' => null,
        ]);
    }
}
