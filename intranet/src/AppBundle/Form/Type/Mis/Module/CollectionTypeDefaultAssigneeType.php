<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module;

use AppBundle\Form\Type\Mis\Application\TypeDefaultAssigneeType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;

class CollectionTypeDefaultAssigneeType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('typeDefaultAssignees', CollectionType::class, [
            'entry_type' => TypeDefaultAssigneeType::class,
        ]);
    }
}
