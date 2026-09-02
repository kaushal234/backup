<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Materials;

use AppBundle\Form\Type\Common\DateTimePickerType;
use AppBundle\Form\Type\Directory\Location\FactoryChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvendorsNewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $news = $builder->getData();

        $builder
            ->add('content', CKEditorType::class, [
                'label' => 'news.fields.content',
                'data' => $news['content'] ?? '<p>News</p>',
                'config_name' => 'simple',
            ])
            ->add('createdBy', PeopleAutocompleteChoiceType::class, [
                'label' => 'news.fields.publisher',
            ])
            ->add('publishedAt', DateTimePickerType::class, [
                'label' => 'evendors_news.published_at',
            ])
            ->add('unpublishedAt', DateTimePickerType::class, [
                'label' => 'evendors_news.unpublished_at',
            ])
            ->add('factories', FactoryChoiceType::class, [
                'multiple' => true,
                'required' => false,
            ])
            ->add('files', FileType::class, [
                'label' => 'news.fields.files',
                'required' => false,
                'multiple' => true,
                'data_class' => null,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'news',
        ]);
    }
}
