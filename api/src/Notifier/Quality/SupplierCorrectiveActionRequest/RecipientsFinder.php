<?php

declare(strict_types=1);

namespace App\Notifier\Quality\SupplierCorrectiveActionRequest;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Client\Exception\SoapException;
use App\Entity\Common\Subscription;
use App\Entity\Directory\People;
use App\Entity\Quality\SupplierCorrectiveActionRequest;
use App\ION\DataProvider\CachedIONItemDataProvider;
use App\ION\Resources\MasterData\Items\Item;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly LocationRepository $locationRepository,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly CachedIONItemDataProvider $itemProvider,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
    ) {
    }

    public function findTos(SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest, $notifyCEO = true): array
    {
        $recipients = [];
        if ($supplierCorrectiveActionRequest->poster instanceof People) {
            $recipients = [$supplierCorrectiveActionRequest->poster];
        }

        $recipients = array_filter([
            ...$recipients,
            $supplierCorrectiveActionRequest->leader,
            $supplierCorrectiveActionRequest->representative,
            ...$this->peopleRepository->findGroupsMembers(['ROLE_QAM', 'ROLE_COO'], $supplierCorrectiveActionRequest->factory),
        ]);

        if (!\in_array($supplierCorrectiveActionRequest->iFactor, [SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_100, SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1000], true)) {
            return [...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients)];
        }

        if (null !== $alvestLocation = $this->locationRepository->findOneBy(['erp' => 900])) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupMembers('ROLE_CMO', $alvestLocation),
            ];

            if (SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1000 === $supplierCorrectiveActionRequest->iFactor) {
                $recipients = [
                    ...$recipients,
                    ...$this->peopleRepository->findGroupsMembers(['ROLE_GTD', 'ROLE_CPO', 'ROLE_COO', 'ROLE_TCOO'], $alvestLocation),
                ];
            }
        }

        if ($notifyCEO) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupMembers('ROLE_CEO', $supplierCorrectiveActionRequest->factory),
            ];
        }

        if (SupplierCorrectiveActionRequest::IMPORTANCE_FACTOR_1000 === $supplierCorrectiveActionRequest->iFactor) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findGroupMembers('ROLE_RCOO', $alvestLocation),
            ];
        }

        $recipients = [
            ...$recipients,
            ...$this->peopleRepository->findGroupsMembers(['ROLE_MLM', 'ROLE_EM'], $supplierCorrectiveActionRequest->factory),
        ];

        return [...array_map(static fn (People $recipient) => $recipient->getEmail(), $recipients)];
    }

    public function findCcs(SupplierCorrectiveActionRequest $supplierCorrectiveActionRequest): array
    {
        $ccs = array_reduce($this->subscriptionRepository->findByResource($supplierCorrectiveActionRequest), static function ($memo, Subscription $subscription) {
            $memo[] = $subscription->getUser()->getEmail();

            return $memo;
        }, []);

        if (SupplierCorrectiveActionRequest::TLD_TO_REVIEW_FORM === $supplierCorrectiveActionRequest->getStatus()) {
            foreach ($supplierCorrectiveActionRequest->getParts() as $part) {
                try {
                    $operation = $this->resourceMetadataCollectionFactory->create(Item::class)->getOperation();

                    /** @var Item|null $item */
                    $item = $this->itemProvider->provide($operation, ['item' => $part->partNumber, 'site' => $supplierCorrectiveActionRequest->factory->getErp()]);
                    if (null !== $item->buyer) {
                        $ccs[] = $item->buyer->emailAddress;
                    }
                } catch (SoapException $e) {
                    // do nothing;
                }
            }
        }

        return $ccs;
    }
}
