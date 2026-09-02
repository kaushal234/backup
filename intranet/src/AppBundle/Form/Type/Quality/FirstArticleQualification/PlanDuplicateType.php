<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\FirstArticleQualification;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanDuplicateType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('targets', FirstArticleQualificationAutocompleteChoiceType::class, [
            'required' => true,
            'multiple' => true,
            'label' => 'first_article_qualification.report.duplicate_targets',
            'translation_domain' => 'first_article_qualification',
            'template' => 'FAQ#{{ id }}',
            'query' => [
                'order' => ['id' => 'ASC'],
                'location' => $options['location'],
                'exists[plan]' => false,
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'location' => null,
            'excludeId' => null,
            'csrf_protection' => false,
        ]);
    }
}
