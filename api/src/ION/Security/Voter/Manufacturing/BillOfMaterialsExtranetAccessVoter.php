<?php

declare(strict_types=1);

namespace App\ION\Security\Voter\Manufacturing;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Client\Exception\SoapException;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\Manufacturing\JobShop\BillOfMaterials\Drawing;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials;
use App\ION\Resources\Manufacturing\JobShop\CustomizedBillOfMaterials\Chapters;
use App\Repository\EquipmentRecordRepository;
use App\Security\Voter\AbstractVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Vote;

class BillOfMaterialsExtranetAccessVoter extends AbstractVoter
{
    public static function getSubscribedServices(): array
    {
        return [...parent::getSubscribedServices(), ...[ResourceMetadataCollectionFactoryInterface::class, CachedIONItemDataProvider::class, EntityManagerInterface::class]];
    }

    public function supports(string $attribute, $subject): bool
    {
        return 'BILL_OF_MATERIAL_EXTRANET_VOTER' === $attribute;
    }

    public function voteOnAttribute(string $attribute, $subject, TokenInterface $token, ?Vote $vote = null): bool
    {
        $user = $token->getUser();
        if (!$user instanceof ExtranetUser) {
            return false;
        }

        $acls = $user->getExtranetUserAcls();
        /** @var EquipmentRecordRepository $erRepository */
        $erRepository = $this->serviceLocator->get(EntityManagerInterface::class)->getRepository(EquipmentRecord::class);

        if ($subject instanceof CustomizedBillOfMaterials) {
            foreach ($acls as $acl) {
                $crt = $acl->getCrt();
                $equipment = $erRepository->getEquipmentByExtranetUserCrtCustomer($crt->getCustomer(), $subject->getProject());
                if (null !== $equipment && $equipment->getSerialNumber() === $subject->getProject()) {
                    return true;
                }
            }

            return false;
        }

        $resolver = new OptionsResolver();
        $resolver->setRequired(['drawing', 'project', 'signalCode']);
        $resolver->setAllowedTypes('drawing', Drawing::class);
        $resolver->setAllowedTypes('project', 'string');
        $resolver->setAllowedTypes('signalCode', 'string');
        $resolver->resolve($subject);

        if (0 === $acls->count()) {
            return false;
        }

        foreach ($acls as $acl) {
            $crt = $acl->getCrt();
            if (!$crt->getCustomer() instanceof Customer) {
                continue;
            }

            $equipment = $erRepository->getEquipmentByExtranetUserCrtCustomer($crt->getCustomer(), $subject['project']);

            if (!$equipment) {
                continue;
            }

            try {
                /** @var ResourceMetadataCollectionFactoryInterface $resourceMetadatafactory */
                $resourceMetadatafactory = $this->serviceLocator->get(ResourceMetadataCollectionFactoryInterface::class);
                $metadata = $resourceMetadatafactory->create(Chapters::class);
                /** @var Chapters|null $items */
                $items = $this->serviceLocator->get(CachedIONItemDataProvider::class)->provide($metadata->getOperation(), [
                    'date' => ($equipment->getGreenTagDate() ?? new \DateTime())->format(\DateTimeInterface::ATOM),
                    'project' => $subject['project'],
                    'site' => $subject['drawing']->getSite(),
                    'signalCodeFilter' => $subject['signalCode'],
                    'signalCodeFilterMethod' => 'Equals',
                    'signalCodeAttribute' => 'engineeringSignalCode',
                ]);
            } catch (SoapException $exception) {
                continue;
            }

            $filteredItems = $items->getItems()->filter(static fn ($item) => $item->partNumber === $subject['drawing']->product);

            if (!$filteredItems->isEmpty()) {
                return true;
            }
        }

        return false;
    }
}
