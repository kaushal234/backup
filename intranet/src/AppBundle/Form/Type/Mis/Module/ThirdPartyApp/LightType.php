<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use ApiBundle\Form\Type\ResourceCollectionType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Mis\Module\ModuleType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;

class LightType extends ModuleType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);

        $builder
            ->add('mainAdmin', PeopleAutocompleteChoiceType::class, [
                'label' => 'mis.modules.fields.main_admin',
                'placeholder' => 'mis.modules.make_selection',
                'required' => true,
            ])
            ->add('sso', CheckboxType::class, [
                'label' => 'mis.modules.fields.sso',
                'required' => false,
            ])
            ->add('mfaUser', CheckboxType::class, [
                'label' => 'mis.modules.fields.mfa_user',
                'required' => false,
            ])
            ->add('mfaAdmin', CheckboxType::class, [
                'label' => 'mis.modules.fields.mfa_admin',
                'required' => false,
            ])
            ->add('passwordPolicyApplied', CheckboxType::class, [
                'label' => 'mis.modules.password_policy_applied',
                'required' => false,
            ])
            ->add('securityLevel', ResourceCollectionType::class, [
                'label' => 'mis.modules.fields.security_level',
                'resource' => 'security_levels',
                'property' => 'name',
            ])
            ->add('securityReviewDateStart', DatePickerType::class, [
                'label' => 'mis.modules.fields.security_review_date_start',
                'required' => false,
            ])
            ->add('securityReviewFrequency', IntegerType::class, [
                'label' => 'mis.modules.fields.security_review_frequency',
                'required' => false,
            ])
            ->add('accountReviewDateStart', DatePickerType::class, [
                'label' => 'mis.modules.fields.account_review_date_start',
                'required' => false,
            ])
            ->add('accountReviewFrequency', IntegerType::class, [
                'label' => 'mis.modules.fields.account_review_frequency',
                'required' => false,
            ])
            ->add('availabilityClassification', ClassificationType::class, [
                'label' => 'mis.modules.fields.availability',
                'required' => false,
            ])
            ->add('integrityClassification', ClassificationType::class, [
                'label' => 'mis.modules.fields.integrity',
                'required' => false,
            ])
            ->add('confidentialityClassification', ClassificationType::class, [
                'label' => 'mis.modules.fields.confidentiality',
                'required' => false,
            ])
        ;
    }
}
