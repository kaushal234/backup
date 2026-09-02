<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DuplicatePeopleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->remove('hidden');
        $builder->remove('disabled');
        $builder->add(
            'acls',
            AclChoiceType::class,
            [
                'user' => $options['source']['@id'],
                'label' => false,
                'required' => false,
                'mapped' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['source']);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return PeopleType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_duplicate_people';
    }
}
