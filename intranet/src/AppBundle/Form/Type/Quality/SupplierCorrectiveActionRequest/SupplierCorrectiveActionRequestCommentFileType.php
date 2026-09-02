<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Quality\SupplierCorrectiveActionRequest;

use ActivityBundle\Form\Type\CommentTypeFile;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SupplierCorrectiveActionRequestCommentFileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('contacts', ChoiceType::class, [
            'label' => 'customers.menu.contacts',
            'required' => false,
            'choices' => $options['contacts'],
            'multiple' => true,
            'expanded' => true,
            'translation_domain' => 'sales_customers',
        ]);
    }

    public function getParent(): string
    {
        return CommentTypeFile::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'log',
            'prefilled_comment' => null,
            'contacts' => [],
        ]);
    }
}
