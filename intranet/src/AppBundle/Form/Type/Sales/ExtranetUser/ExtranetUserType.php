<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\ExtranetUser;

use AppBundle\Form\Type\AddressType;
use AppBundle\Form\Type\GenderChoiceType;
use AppBundle\Form\Type\PhoneType;
use AppBundle\Form\Type\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ExtranetUserType extends AbstractType
{
    protected $fieldsRestricted = [
        'password',
        'token',
    ];

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('extranetUserProfile', ExtranetUserProfileType::class, [
                'label' => false,
                'addProfile' => $options['addProfile'],
            ])
            ->add('gender', GenderChoiceType::class, [
                'label' => 'contacts.fields.gender',
                'required' => false,
                'placeholder' => 'contacts.make_selection',
            ])
            ->add('lastname', TextType::class, [
                'label' => 'contacts.fields.lastname',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'contacts.fields.firstname',
            ])
            ->add('email', EmailType::class, [
                'label' => 'contacts.fields.email',
                'constraints' => [
                    new Assert\Email(['message' => 'email']),
                ],
            ])
            ->add('phones', CollectionType::class, [
                'entry_type' => PhoneType::class,
                'entry_options' => [
                    'label' => false,
                ],
                'label' => 'contacts.fields.phone',
                'required' => (bool) $options['add'],
                'data' => $options['add'] ? [0 => []] : $options['phones'],
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => 'app_phone',
            ])
            ->add('address', AddressType::class, [
                'label' => 'address.name',
                'required' => false,
                'country' => false,
            ])
        ;

        $builder->remove('disabled');

        if ($options['add']) {
            $builder->remove('username');
        }

        if (!$this->authorizationChecker->isGranted('ACL_SUPERUSER')) {
            foreach ($this->fieldsRestricted as $name) {
                $builder->remove($name);
            }
        }

        if ($options['addProfile']) {
            $builder->remove('hidden');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'contacts',
            'add' => false,
            'addProfile' => false,
            'phones' => [],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return UserType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_sales_extranet_users';
    }
}
