<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('countStartAdminUsers', IntegerType::class, [
                'label' => 'mis.account_review.fields.count_start_admin_users',
                'required' => false,
            ])
            ->add('countEndAdminUsers', IntegerType::class, [
                'label' => 'mis.account_review.fields.count_end_admin_users',
                'required' => false,
            ])
            ->add('adminsComment', TextareaType::class, [
                'label' => 'mis.account_review.fields.admins_comment',
                'required' => false,
            ])
            ->add('adminAccountsConfirmed', CheckboxType::class, [
                'label' => 'mis.account_review.fields.admin_accounts_confirmed',
                'required' => true,
            ])
            ->add('countAppUsers', IntegerType::class, [
                'label' => 'mis.account_review.fields.count_app_users',
                'required' => false,
            ])
            ->add('countStartMembers', IntegerType::class, [
                'label' => 'mis.account_review.fields.count_start_members',
                'required' => false,
            ])
            ->add('countEndMembers', IntegerType::class, [
                'label' => 'mis.account_review.fields.count_end_members',
                'required' => false,
            ])
            ->add('countDisabledAccounts', IntegerType::class, [
                'label' => 'mis.account_review.fields.count_disabled_accounts',
                'required' => false,
            ])
            ->add('disabledAccountsComment', TextareaType::class, [
                'label' => 'mis.account_review.fields.disabled_accounts_comment',
                'required' => false,
            ])
            ->add('countEnabledAccounts', IntegerType::class, [
                'label' => 'mis.account_review.fields.count_enabled_accounts',
                'required' => false,
            ])
            ->add('enabledAccountsComment', TextareaType::class, [
                'label' => 'mis.account_review.fields.enabled_accounts_comment',
                'required' => false,
            ])
            ->add('userAccountsConfirmed', CheckboxType::class, [
                'label' => 'mis.account_review.fields.user_accounts_confirmed',
                'required' => true,
            ])
            ->add('file', FileType::class, [
                'label' => 'file_type.file_upload',
                'translation_domain' => 'file_type',
                'required' => true,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'mis',
        ]);
    }
}
