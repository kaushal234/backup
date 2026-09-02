<?php

declare(strict_types=1);

namespace App\MessageHandler\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Message\Sales\CustomerMainRepresentativeUpdate;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CustomerMainRepresentativeUpdateHandler
{
    private readonly IriConverterInterface $iriConverter;
    private readonly EntityManagerInterface $entityManager;

    public function __construct(IriConverterInterface $iriConverter, EntityManagerInterface $entityManager)
    {
        $this->iriConverter = $iriConverter;
        $this->entityManager = $entityManager;
    }

    public function __invoke(CustomerMainRepresentativeUpdate $message)
    {
        /** @var Customer $customer */
        $customer = $this->iriConverter->getResourceFromIri($message->getResourceIri());
        /** @var People $asm */
        $asm = $this->iriConverter->getResourceFromIri($message->getAsmIri());

        $crtRepository = $this->entityManager->getRepository(CustomerRelationshipTeam::class);
        /** @var CustomerRelationshipTeam $crt */
        foreach ($crtRepository->findBy(['customer' => $customer]) as $crt) {
            $crt->setSalesRepresentative($asm);
            $this->entityManager->persist($crt);
        }

        $this->entityManager->flush();
    }
}
