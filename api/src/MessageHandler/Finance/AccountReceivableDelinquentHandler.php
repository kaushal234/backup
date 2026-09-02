<?php

declare(strict_types=1);

namespace App\MessageHandler\Finance;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\Location;
use App\Entity\Finance\AccountReceivable;
use App\Message\Finance\AccountReceivableDelinquent;
use App\Repository\Finance\AccountReceivableRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class AccountReceivableDelinquentHandler
{
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    public function __invoke(AccountReceivableDelinquent $message)
    {
        /** @var Location|null $location */
        $location = null !== $message->getLocationIri() ? $this->iriConverter->getResourceFromIri($message->getLocationIri()) : null;

        /** @var AccountReceivableRepository $repository */
        $repository = $this->entityManager->getRepository(AccountReceivable::class);

        foreach ($repository->findDelinquents($location) as $accountReceivable) {
            $accountReceivable->delinquent = true;
            $this->entityManager->persist($accountReceivable);
        }

        $this->entityManager->flush();
    }
}
