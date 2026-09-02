<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditPeopleType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        foreach (array_keys($builder->all()) as $key) {
            if (!\in_array($key, $options['authorizedFields'], true)) {
                if (\in_array($key, ['username', 'email', 'phones'], true)) {
                    $builder->remove($key);
                    continue;
                }
                $field = $builder->get($key);
                $field->setDisabled(true);
            }
        }

        if (null !== $builder->getData()->premise) {
            $builder->remove('address');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
            'authorizedFields' => [],
            'editType' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return PeopleType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_edit_people';
    }
}
