<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Survey;

use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Quality\Custom\ItemsPerPageType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SurveyFilters extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'survey.fields.name',
                'required' => false,
            ])
           ->add('createdBy', PeopleAutocompleteChoiceType::class, [
               'label' => 'survey.fields.createdBy',
               'required' => false,
               'property_path' => '[createdBy]',
           ])
            ->add('itemsPerPage', ItemsPerPageType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'surveys',
            'csrf_protection' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_surveys_filters';
    }
}
