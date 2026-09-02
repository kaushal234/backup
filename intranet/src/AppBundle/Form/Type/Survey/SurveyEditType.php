<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Survey;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class SurveyEditType extends SurveyType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('id', HiddenType::class);
    }

    public function getParent(): string
    {
        return SurveyType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_tool_status_choice';
    }
}
