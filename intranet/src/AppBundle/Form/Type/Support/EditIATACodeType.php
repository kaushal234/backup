<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Support;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditIATACodeType extends AbstractType
{
    protected $fieldsRestricted = [
        'type',
    ];

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach ($this->fieldsRestricted as $name) {
            $builder->remove($name);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return IATACodeType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'support',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_edit_iata_codes';
    }
}
