<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Directory;

use AppBundle\Form\Type\Common\AirportChoiceType;
use AppBundle\Form\Type\Directory\BusinessUnit\BusinessUnitAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Department\DepartmentChoiceType;
use AppBundle\Form\Type\Directory\Division\DivisionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Location\RegionAutocompleteChoiceType;
use AppBundle\Form\Type\Directory\Position\PositionAutocompleteChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class PeoplePremiseFilterType extends AbstractType
{
    private readonly AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('position', PositionAutocompleteChoiceType::class, [
                'required' => false,
                'multiple' => true,
            ])
            ->add('businessUnit', BusinessUnitAutocompleteChoiceType::class, [
                'label' => 'directory.location.fields.businessUnit',
                'required' => false,
            ])
            ->add('department', DepartmentChoiceType::class, [
                'label' => 'directory.department.name',
                'translation_domain' => 'directory',
                'required' => false,
            ])
            ->add('region', RegionAutocompleteChoiceType::class, [
                'property_path' => '[businessUnit.region]',
                'label' => 'menu.region.title',
                'required' => false,
                'multiple' => true,
                'translation_domain' => 'messages',
            ])
            ->add('division', DivisionAutocompleteChoiceType::class, [
                'property_path' => '[businessUnit.region.subDivision.division]',
                'label' => 'menu.division.title',
                'required' => false,
                'multiple' => true,
                'translation_domain' => 'messages',
            ])
            ->add('airport', AirportChoiceType::class, [
                'property_path' => '[closestAirport]',
                'required' => false,
                'label' => 'directory.closest_airport.label',
                'translation_domain' => 'directory',
            ]);
        if ($this->authorizationChecker->isGranted('FEATURE_DOWNLOAD_DIRECTORY')) {
            $builder->add('download', SubmitType::class, [
                'label' => 'menu.download',
                'translation_domain' => 'messages',
                'attr' => ['class' => 'btn btn-primary btn-danger text-uppercase'],
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'directory',
        ]);
    }
}
