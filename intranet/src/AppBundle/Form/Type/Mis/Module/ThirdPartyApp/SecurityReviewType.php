<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use ApiBundle\Form\Type\ResourceCollectionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SecurityReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('securityLevel', ResourceCollectionType::class, [
                'label' => 'mis.modules.fields.security_level',
                'resource' => 'security_levels',
                'property' => 'name',
            ])
            ->add('securityLevelComment', TextareaType::class, [
                'label' => 'mis.security_review.fields.security_level_comment',
                'required' => false,
            ])
            ->add('isPasswordPolicyApplied', CheckboxType::class, [
                'label' => 'mis.security_review.fields.is_password_policy_applied',
                'required' => false,
            ])
            ->add('isMFAAdminApplied', CheckboxType::class, [
                'label' => 'mis.security_review.fields.is_mfa_admin_applied',
                'required' => false,
            ])
            ->add('isMFAUserApplied', CheckboxType::class, [
                'label' => 'mis.security_review.fields.is_mfa_user_applied',
                'required' => false,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'mis.security_review.fields.comment',
                'required' => false,
            ])
            ->add('file', FileType::class, [
                'label' => 'file_type.file_upload',
                'translation_domain' => 'file_type',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
        ]);
    }
}
