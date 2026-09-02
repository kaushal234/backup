<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Parts\SBSparePartsRequest;
use App\Entity\Service\CustomerServiceRecord\ServiceBulletinCustomerServiceRecord;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Dto\ServiceBulletin;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class CustomerServiceRecordServiceBulletinLineListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $entityManager,
        private readonly DenormalizerInterface $denormalizer,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => [
                ['postLoad', EventPriorities::POST_READ],
            ],
        ];
    }

    public function postLoad(RequestEvent $event): void
    {
        $customerServiceRecord = $event->getRequest()->attributes->get('data');
        $request = $event->getRequest();
        $operation = $request->attributes->get('_api_operation');
        if (!$customerServiceRecord instanceof ServiceBulletinCustomerServiceRecord || Request::METHOD_GET !== $request->getMethod() || !$operation instanceof Get) {
            return;
        }

        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $queryBuilder
            ->select('sb.id AS sbLegacyId', 'sb.status AS status', 'sb.category AS category', 'sb.title AS title', 'sbl.spr_id AS sprLegacyId')
            ->from('sb_lines', 'sbl')
            ->join('sbl', 'sb', 'sb', 'sbl.parent_id = sb.id')
            ->where('sbl.id = :id')
            ->setParameter('id', $customerServiceRecord->serviceBulletinLinesLegacyId)
        ;

        $data = $queryBuilder->fetchAssociative();
        $serviceBulletin = $this->denormalizer->denormalize($data, ServiceBulletin::class);
        $serviceBulletin->sparePartsRequest = new ArrayCollection($this->entityManager->getRepository(SBSparePartsRequest::class)->findBy(['legacyId' => $data['sprLegacyId']]));

        $customerServiceRecord->serviceBulletin = $serviceBulletin;
    }
}
