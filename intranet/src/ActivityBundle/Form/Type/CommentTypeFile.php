<?php

declare(strict_types=1);

namespace ActivityBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;

class CommentTypeFile extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        parent::buildForm($builder, $options);
        $builder->add('file', FileType::class, ['required' => false]);
    }

    public function getParent(): string
    {
        return CommentType::class;
    }
}
