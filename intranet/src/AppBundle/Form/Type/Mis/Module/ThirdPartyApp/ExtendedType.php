<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Mis\Module\ThirdPartyApp;

use Symfony\Component\Form\FormBuilderInterface;

class ExtendedType extends LightType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
    }
}
