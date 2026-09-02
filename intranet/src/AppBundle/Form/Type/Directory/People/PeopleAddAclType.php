<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use ApiBundle\Form\Type\ResourceCollectionType;
use AppBundle\Form\Type\Common\DatePickerType;
use AppBundle\Form\Type\Directory\GroupAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\LessThan;

class PeopleAddAclType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('group', GroupAutocompleteChoiceType::class, [
                'label' => 'group',
                'multiple' => true,
                'query' => [
                    'order' => [
                        'name' => 'ASC',
                    ],
                    'restricted' => $options['groupFilter'] ?: null,
                ],
            ])
            ->add('location', ResourceCollectionType::class, [
                'label' => 'location',
                'resource' => 'locations',
                'property' => 'name',
            ])
            ->add('expiredAt', DatePickerType::class, [
                'label' => 'menu.people.expire_at',
                'translation_domain' => 'messages',
                'required' => $options['groupFilter'],
                'help' => 'menu.people.help_expire',
                'constraints' => [
                    new GreaterThan(['value' => date('Y-m-d')]),
                    new LessThan(['value' => (new \DateTime())->modify('+ 4 months')->format('Y-m-d')]),
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'groupFilter' => false,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_people_add_acl';
    }
}
