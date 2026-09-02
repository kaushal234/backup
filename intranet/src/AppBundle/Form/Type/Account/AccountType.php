<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Account;

use AppBundle\Form\Type\Directory\People\EditPeopleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public static $fieldsToKeep = [
        'lastname',
        'firstname',
        'nickname',
        'alternateEmail',
        'image',
        'phones',
        'address',
        'locale',
        'gender',
        'premise',
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($builder as $field) {
            if (!\in_array($field->getName(), self::$fieldsToKeep, true)) {
                $builder->remove($field->getName());
            }
        }

        foreach (array_keys($builder->all()) as $key) {
            if (!\in_array($key, $options['authorizedFields'], true)) {
                $field = $builder->get($key);
                $field->setDisabled(true);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
            'authorizedFields' => null,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return EditPeopleType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_account';
    }
}
