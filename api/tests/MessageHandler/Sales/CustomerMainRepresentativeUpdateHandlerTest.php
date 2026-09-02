<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\Customer;
use App\Entity\Sales\CustomerRelationshipTeam;
use App\Message\Sales\CustomerMainRepresentativeUpdate;
use App\MessageHandler\Sales\CustomerMainRepresentativeUpdateHandler;
use App\Repository\Sales\CustomerRelationshipTeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CustomerMainRepresentativeUpdateHandlerTest extends KernelTestCase
{
    use ProphecyTrait;

    public function testHandlerWhenCustomerMainContactIsUpdated(): void
    {
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $crtRepositoryMock = $this->getMockBuilder(CustomerRelationshipTeamRepository::class)->disableOriginalConstructor()->onlyMethods(['findBy'])->getMock();
        $message = new CustomerMainRepresentativeUpdate('/customer/1', '/people/1');
        $customerRelationshipTeam = $this->prophesize(CustomerRelationshipTeam::class);

        $iriConverterProphecy->getResourceFromIri('/customer/1')->shouldBeCalledTimes(1)->willReturn($customer = new Customer());
        $iriConverterProphecy->getResourceFromIri('/people/1')->shouldBeCalledTimes(1)->willReturn($asm = new People());

        $customerRelationshipTeam->setSalesRepresentative($asm)->shouldBeCalledOnce();

        $crtRepositoryMock->expects($this->once())->method('findBy')->with(['customer' => $customer])->willReturn([
            $customerRelationshipTeam->reveal(),
        ]);

        $entityManagerProphecy->getRepository(CustomerRelationshipTeam::class)->shouldBeCalledOnce()->willReturn($crtRepositoryMock);
        $entityManagerProphecy->persist($customerRelationshipTeam->reveal())->shouldBeCalledOnce();
        $entityManagerProphecy->flush()->shouldBeCalledOnce();

        $handler = new CustomerMainRepresentativeUpdateHandler($iriConverterProphecy->reveal(), $entityManagerProphecy->reveal());
        $handler($message);
    }
}
