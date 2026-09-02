<?php

declare(strict_types=1);

namespace AppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ImageType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('file', FileType::class, [
                'label' => 'image.fields.file',
                'required' => 'false',
                'multiple' => $options['multiple'],
            ]);
        if (true === $options['deletable']) {
            $builder->add('delete', CheckboxType::class, [
                'label' => 'image.fields.delete',
                'translation_domain' => 'messages',
                'required' => 'false',
            ]);
        }
        if (true === $options['allow_resize']) {
            $builder->add('imageSize', ChoiceType::class, [
                'label' => 'news.size.image_size',
                'choices' => [
                    'news.size.none' => null,
                    'news.size.square' => [
                        'news.size.large' => 'large',
                        'news.size.medium' => ' medium',
                        'news.size.small' => 'small',
                    ],
                    'news.size.horizontal_rectangle' => [
                        'news.size.large_horizontal' => 'large_horizontal',
                        'news.size.medium_horizontal' => 'medium_horizontal',
                        'news.size.small_horizontal' => 'small_horizontal',
                    ],
                    'news.size.vertical_rectangle' => [
                        'news.size.large_vertical' => 'large_vertical',
                        'news.size.medium_vertical' => 'medium_vertical',
                        'news.size.small_vertical' => 'small_vertical',
                    ],
                ],
                'translation_domain' => 'news',
                'expanded' => false,
                'required' => false,
                'multiple' => false,
                'empty_data' => null,
            ]);
        }

        $builder->addEventListener(FormEvents::POST_SET_DATA, fn (FormEvent $event) => $this->onPostSetData($event));
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        parent::buildView($view, $form, $options);

        $view->vars['image_url'] = $options['image_url'];
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired([
            'image_url',
        ]);
        $resolver->setDefaults([
            'translation_domain' => 'messages',
            'deletable' => true,
            'multiple' => false,
            'allow_resize' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_image';
    }

    /**
     * Remove the delete field if there is no image already defined.
     */
    public function onPostSetData(FormEvent $event)
    {
        $form = $event->getForm();
        $imageUrl = $form->getConfig()->getOption('image_url');

        if (empty($imageUrl)) {
            $form->remove('delete');
        }
    }
}
