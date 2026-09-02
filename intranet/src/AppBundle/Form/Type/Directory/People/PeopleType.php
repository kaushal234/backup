<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Directory\People;

use ApiBundle\Form\Type\ResourceCollectionType;
use AppBundle\Form\Type\AddressType;
use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\ContractType\ContractTypeChoiceType;
use AppBundle\Form\Type\Directory\Position\PositionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Premise\PremiseChoiceType;
use AppBundle\Form\Type\GenderChoiceType;
use AppBundle\Form\Type\ImageType;
use AppBundle\Form\Type\PhoneType;
use AppBundle\Form\Type\UserType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PercentType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraints\NotBlank;

class PeopleType extends AbstractType
{
    protected array $fieldsRestricted = [
        'password',
        'hidden',
        'disabled',
        'erpLogin',
        'windowsLogin',
        'token',
    ];

    private readonly AuthorizationCheckerInterface $authorizationChecker;

    private readonly array $languageChoices;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker, array $languageChoices)
    {
        $this->authorizationChecker = $authorizationChecker;
        $this->languageChoices = array_flip($languageChoices);
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $people = $builder->getData();
        $closestAirport = $people['closestAirport'] ?? null;
        $builder
            ->add('lastname', TextType::class, [
                'label' => 'directory.people.fields.lastname',
            ])
            ->add('firstname', TextType::class, [
                'label' => 'directory.people.fields.firstname',
            ])
            ->add('gender', GenderChoiceType::class, [
                'label' => 'directory.people.fields.gender',
                'required' => false,
            ])
            ->add('nickname', TextType::class, [
                'label' => 'directory.people.fields.nickname',
                'required' => false,
            ])
            ->add('email', EmailType::class, [
                'label' => 'directory.people.fields.email',
                'constraints' => [
                    new Assert\Email(['message' => 'email']),
                ],
            ])
            ->add('premise', PremiseChoiceType::class, [
                'label' => 'directory.premise.list.list',
                'placeholder' => 'OffSite Location',
            ])
            ->add('closestAirport', AirportChoiceType::class, [
                'label' => 'fields.closest_airport',
                'translation_domain' => 'messages',
                'required' => false,
            ])
            ->add('alternateEmail', EmailType::class, [
                'label' => 'directory.people.fields.email_alternate',
                'required' => false,
                'constraints' => [
                    new Assert\Email(['message' => 'email']),
                ],
            ])
            ->add('jobTitle', TextType::class, [
                'label' => 'directory.people.fields.jobTitle',
            ])
            ->add('image', ImageType::class, [
                'label' => 'image.name_photo',
                'required' => false,
                'mapped' => false,
                'image_url' => isset($people['photo']) && null !== $people['photo'] ? $people['photo']['filePath'] : null,
            ])
            ->add('phones', CollectionType::class, [
                'entry_type' => PhoneType::class,
                'label' => 'directory.people.fields.phones',
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'allow_add' => true,
                'allow_delete' => true,
                'prototype' => 'app_phone',
            ])
            ->add('address', AddressType::class, [
                'label' => 'address.name',
                'required' => false,
            ])
            ->add('windowsLogin', TextType::class, [
                'label' => 'directory.people.fields.windowsLogin',
                'required' => false,
            ])
            ->add('businessUnit', BusinessUnitAutocompleteChoiceType::class, [
                'label' => 'directory.people.fields.businessUnit',
            ])
            ->add('legalEntity', BusinessUnitAutocompleteChoiceType::class, [
                'label' => 'directory.people.fields.legalEntity',
            ])
            ->add('position', PositionAutocompleteChoiceType::class, [
                'label' => 'directory.people.fields.position',
            ])
            ->add('positionCategory', TextType::class, [
                'label' => 'directory.position_categories.singular',
                'disabled' => true,
                'required' => false,
                'mapped' => false,
            ])
            ->add('department', ResourceCollectionType::class, [
                'label' => 'directory.people.fields.department',
                'resource' => 'departments',
                'property' => 'name',
                'expanded' => false,
                'multiple' => false,
                'placeholder' => 'directory.people.make_selection',
            ])
            ->add('supervisor', PeopleAutocompleteChoiceType::class, [
                'label' => 'directory.people.fields.supervisor',
                'data_disabled' => [$people],
                'constraints' => [
                    new NotBlank(),
                ],
            ])
            ->add('mentor', PeopleAutocompleteChoiceType::class, [
                'label' => 'directory.people.fields.mentor',
                'data_disabled' => [$people],
                'required' => false,
            ])
            ->add('locale', ChoiceType::class, [
                'label' => 'directory.people.fields.prefered_locale',
                'choices' => $this->languageChoices,
            ])
            ->add('contractType', ContractTypeChoiceType::class, [
                'label' => 'directory.people.fields.contract_type',
                'placeholder' => 'directory.contract_type.make_selection',
            ])
            ->add('coefficient', PercentType::class, [
                'label' => 'directory.people.fields.coefficient',
                'type' => 'integer',
                'data' => $people['coefficient'] ?? 100,
            ])
        ;

        if (($options['editType'] ?? false) === true && !$this->authorizationChecker->isGranted('FEATURE_PEOPLE_UPDATE_VOTER', $people['@id'])) {
            foreach ($this->fieldsRestricted as $name) {
                $builder->remove($name);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'translation_domain' => 'directory',
            'allow_extra_fields' => true,
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return UserType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix(): string
    {
        return 'app_people';
    }
}
