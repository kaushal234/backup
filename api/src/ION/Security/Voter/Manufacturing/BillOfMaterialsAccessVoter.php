<?php

declare(strict_types=1);

namespace App\ION\Security\Voter\Manufacturing;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\Manufacturing\BillOfMaterialsSecurity;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterialsIdentifiersInterface;
use App\Security\Voter\AbstractVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class BillOfMaterialsAccessVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[CachedIONItemDataProvider::class, ResourceMetadataCollectionFactoryInterface::class]];
    }

    protected function supports(string $attribute, $subject): bool
    {
        return 'BILL_OF_MATERIAL_VENDOR_VOTER' === $attribute && $subject instanceof BillOfMaterialsIdentifiersInterface;
    }

    /**
     * @param BillOfMaterialsIdentifiersInterface $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();

        if ($user instanceof People) {
            return true;
        }

        if (!$user instanceof VendorUser) {
            return false;
        }

        /** @var ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory */
        $resourceMetadataFactory = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class);
        $metadata = $resourceMetadataFactory->create(BillOfMaterialsSecurity::class);

        /** @var BillOfMaterialsSecurity|null $security */
        $security = $this->serviceLocator->get(CachedIONItemDataProvider::class)->provide($metadata->getOperation(), [
            'contactCode' => $user->contact->contactCode,
            'project' => $subject->getProject(),
            'item' => $subject->getItem(),
            'site' => $subject->getSite(),
        ]);

        return null !== $security && $security->isGranted;
    }
}
