<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Parts;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class EditTransportationNoteType extends AbstractType
{
    protected $fieldsRestricted = [
        'country',
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
        return TransportationNoteType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return '';
    }
}
