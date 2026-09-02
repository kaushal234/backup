<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SearchType as CoreSearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class SearchType extends AbstractType
{
    public function __construct(private readonly AuthorizationCheckerInterface $authorizationChecker)
    {
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', CoreSearchType::class, [
                'label' => 'directory.people.search.name',
            ])
            ->add('hidden', CheckboxType::class, [
                'label' => 'directory.people.search.hidden',
                'required' => false,
            ])
        ;
        if (!($this->authorizationChecker->isGranted('ACL_SUPERUSER')
            || $this->authorizationChecker->isGranted('ACL_GG_HR')
            || $this->authorizationChecker->isGranted('ACL_GG_MIS'))
        ) {
            $builder->remove('hidden');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_people_search';
    }
}
