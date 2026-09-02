<?php

declare(strict_types=1);

namespace AppBundle\Filters\Type\Directory;

use ApiBundle\Model\User;
use AppBundle\Form\Type\Common\SelectFormType;
use AppBundle\Security\RoleProvider\AclProvider;
use AppBundle\Service\DataProvider;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

/**
 * @deprecated This type is deprecated because it is not using an autocomplete system.
 * Don't use it and created an autocomplete version. @see AutocompleteChoiceType
 */
class PositionClassificationBusinessUnitChoiceType extends AbstractType
{
    private readonly DataProvider $dataProvider;
    private readonly Security $security;
    private readonly AclProvider $aclProvider;
    private readonly RouterInterface $router;

    public function __construct(DataProvider $dataProvider, Security $security, AclProvider $aclProvider, RouterInterface $router)
    {
        $this->dataProvider = $dataProvider;
        $this->security = $security;
        $this->aclProvider = $aclProvider;
        $this->router = $router;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
            'translation_domain' => 'directory',
            'method' => Request::METHOD_GET,
            'label' => 'directory.business_unit.name',
            'choices' => function (Options $options) {
                $businessUnits = $this->dataProvider->findAll('business_units', [], ['name']);

                $allowedBusinessUnits = [];
                if ($this->security->isGranted('FEATURE_POSITION_CLASSIFICATION_WRITE_FULL') || $this->security->isGranted('MOO_ESM')) {
                    $allowedBusinessUnits = $businessUnits;
                }

                if (empty($allowedBusinessUnits) && $this->security->isGranted('FEATURE_POSITION_CLASSIFICATION_WRITE_BU')) {
                    /** @var User $user */
                    $user = $this->security->getUser();
                    $allowedLocations = array_unique(preg_filter('/^.*GG_HR_(\d+)$/', '/locations/$1', $this->aclProvider->loadRolesByUser($user)));
                    $allowedBusinessUnits = $this->dataProvider->findAll(
                        'business_units',
                        ['location' => array_map(static fn (string $location) => $location, $allowedLocations)]
                    );
                }

                $indexedBusinessUnits = [];
                foreach ($allowedBusinessUnits as $businessUnit) {
                    $indexedBusinessUnits[$businessUnit['name']] = $this->router->generate('position_classifications_edit', ['id' => $businessUnit['id']]);
                }

                return ['' => ''] + $indexedBusinessUnits;
            },
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent(): string
    {
        return SelectFormType::class;
    }
}
