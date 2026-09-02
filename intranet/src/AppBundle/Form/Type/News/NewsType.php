<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\News;

use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Common\DateTimePickerType;
use AppBundle\Form\Type\Directory\Department\DepartmentChoiceType;
use AppBundle\Form\Type\Directory\Division\DivisionChoiceType;
use AppBundle\Form\Type\Directory\People\PeopleAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Premise\PremiseChoiceType;
use AppBundle\Form\Type\ImageType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $news = $builder->getData();
        $newsFiles = array_map(
            static fn ($news) => $news['filePath'] ?? null,
            $news['files'] ?? []
        );

        $builder
            ->add('title', TextType::class, [
                'label' => 'news.fields.title',
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'news.fields.content',
                'data' => $news['content'] ?? '<p>News</p>',
                'config_name' => 'simple',
            ])
            ->add('people', PeopleAutocompleteChoiceType::class, [
                'label' => 'news.fields.publisher',
            ])
            ->add('category', NewsCategoryChoiceType::class, [
                'label' => 'news.fields.category',
                'required' => false,
            ])
            ->add('date', DateTimePickerType::class, [
                'label' => 'news.fields.date',
                'data' => $news['date'] ?? date(DatePickerType::DEFAULT_INPUT_FORMAT),
            ])
            ->add('files', CollectionType::class, [
                'label' => 'news.fields.max_images_warning',
                'entry_type' => ImageType::class,
                'allow_add' => true,
                'prototype' => true,
                'by_reference' => false,
                'required' => false,
                'entry_options' => [
                    'image_url' => static function ($index) use ($newsFiles) {
                        return $newsFiles[$index] ?? null;
                    },
                    'deletable' => true,
                    'multiple' => true,
                    'allow_resize' => true,
                ],
            ])
            ->add('department', DepartmentChoiceType::class, [
                'label' => 'directory.people.fields.department',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('division', DivisionChoiceType::class, [
                'label' => 'menu.division.title',
                'required' => false,
                'translation_domain' => 'messages',
            ])
            ->add('premise', PremiseChoiceType::class, [
                'label' => 'directory.premise.title',
                'required' => false,
                'translation_domain' => 'directory',
            ])
            ->add('banner', CheckboxType::class, [
                'label' => 'news.fields.banner',
                'required' => false,
            ])
            ->add('majorIncident', CheckboxType::class, [
                'label' => 'news.fields.major_incident',
                'required' => false,
            ])
            ->add('bannerText', TextType::class, [
                'label' => 'news.fields.banner_text',
                'required' => false,
                'data' => $news['bannerText'] ?? '',
                'attr' => [
                    'maxlength' => 210,
                    'placeholder' => 'news.fields.banner_text_placeholder',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'news',
        ]);
    }

    public function getName(): string
    {
        return 'app_news_form';
    }
}
