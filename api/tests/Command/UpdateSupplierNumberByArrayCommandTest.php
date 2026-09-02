<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\UpdateSupplierNumberByArrayCommand;
use App\Entity\Directory\Location;
use App\Entity\Quality\NonConformity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class UpdateSupplierNumberByArrayCommandTest extends KernelTestCase
{
    use ProphecyTrait;

    /**
     * @var string
     */
    final public const COMMAND = 'tld:supplier-number:bulk_update';

    public function testUpdateNonConformityUsingArrayValues()
    {
        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $propertyAccessorProphecy = $this->prophesize(PropertyAccessorInterface::class);
        $repositoryMock = $this->createMock(EntityRepository::class);

        $entityObject = (new NonConformity())->setSupplierNumber('1000');
        $entityObject->location = (new Location())->setErp(640);
        $entityObject->setSupplierErp(500);

        $emProphecy->getRepository(NonConformity::class)->shouldBeCalledTimes(1)->willReturn($repositoryMock);
        $repositoryMock->expects($this->once())->method('findAll')->willReturn([$entityObject]);
        $propertyAccessorProphecy->getValue(Argument::any(), Argument::any())->shouldBeCalled()->willReturn($entityObject->getSupplierErp());
        $emProphecy->persist($entityObject)->shouldBeCalledOnce();
        $emProphecy->flush()->shouldBeCalled();

        self::bootKernel();
        $application = new Application(self::$kernel);
        $updateCommand = new UpdateSupplierNumberByArrayCommand($emProphecy->reveal(), $propertyAccessorProphecy->reveal());
        $updateCommand->newSupplierValues = ['500-1000' => '2000'];
        $updateCommand->erps = [220];
        $application->addCommand($updateCommand);

        $command = $application->find(self::COMMAND);
        (new CommandTester($command))->execute([
            'command' => self::COMMAND,
            'entity' => 'App\Entity\Quality\NonConformity',
        ]);

        $this->assertSame($entityObject->getSupplierNumber(), '2000');
    }
}
