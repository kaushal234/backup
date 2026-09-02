<?php

declare(strict_types=1);

namespace App\Tests\Security\JWT\PayloadGenerator;

use App\Entity\Purchasing\VendorUser;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerContactManager;
use App\ION\Manager\MasterData\BusinessPartners\BusinessPartnerManager;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\Security\JWT\PayloadGenerator\VendorUserPayloadGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PayloadGeneratorTest extends TestCase
{
    private $contactManager;
    private $businessPartnerManager;
    private $normalizer;
    private $payloadGenerator;

    private $entityManager;

    protected function setUp(): void
    {
        $this->contactManager = $this->createMock(BusinessPartnerContactManager::class);
        $this->businessPartnerManager = $this->createMock(BusinessPartnerManager::class);
        $this->normalizer = $this->createMock(NormalizerInterface::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);

        $this->payloadGenerator = new VendorUserPayloadGenerator(
            $this->normalizer,
            $this->contactManager,
            $this->businessPartnerManager,
            $this->entityManager,
        );
    }

    public function testNoDuplicateBusinessPartnerProcessing()
    {
        $user = $this->createMock(VendorUser::class);
        $user->method('getErpIdentifier')->willReturn('user_erp_identifier');

        $contact = $this->createMock(BusinessPartnerContact::class);

        $businessPartner1 = $this->createMock(BusinessPartner::class);
        $businessPartner1->code = 'BP1';
        $businessPartner2 = $this->createMock(BusinessPartner::class);
        $businessPartner2->code = 'BP1';
        $businessPartner3 = $this->createMock(BusinessPartner::class);
        $businessPartner3->code = 'BP2';

        $businessPartnersCollection = new ArrayCollection([$businessPartner1, $businessPartner2, $businessPartner3]);
        $contact->method('getBusinessPartners')->willReturn($businessPartnersCollection);

        $this->contactManager->method('findByErpIdentifier')->willReturn($contact);

        $this->businessPartnerManager
            ->expects($this->exactly(2))
            ->method('findSupplier')
            ->withConsecutive(['BP1'], ['BP2'])
            ->willReturnOnConsecutiveCalls($businessPartner1, $businessPartner3);

        $payload = [];
        $this->payloadGenerator->generate($payload, $user);
    }
}
