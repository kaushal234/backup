<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Sales\Competitor;

use AppBundle\Form\Type\ImageType;
use AppBundle\Form\Type\Sales\Catalogue\ProductTypeChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompetitorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $competitor = $builder->getData();

        $builder
            ->add('name', TextType::class, [
                'label' => 'competitors.fields.name',
            ])
            ->add('shortDescription', TextType::class, [
                'label' => 'competitors.fields.short_description',
                'required' => false,
            ])
            ->add('description', TextType::class, [
                'label' => 'competitors.fields.description',
                'required' => false,
            ])
            ->add('url', UrlType::class, [
                'label' => 'competitors.fields.url',
                'required' => false,
            ])
            ->add('productTypes', ProductTypeChoiceType::class, [
                'label' => 'competitors.fields.product_types',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
            ])
            ->add('logo', ImageType::class, [
                'label' => false,
                'required' => false,
                'mapped' => false,
                'image_url' => isset($competitor['logo']) && null !== $competitor['logo'] ? $competitor['logo']['filePath'] : null,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'sales_competitors',
        ]);
    }

    public function getName(): string
    {
        return 'app_sales_competitor';
    }
}
