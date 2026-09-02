<?php

declare(strict_types=1);

namespace App\Tests\Repository\Directory;

use App\Entity\Directory\People;
use App\Entity\Directory\PeopleFile;
use App\Entity\Directory\Phone;
use App\Repository\Directory\PeopleRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class PeopleRepositoryTest extends TestCase
{
    public function testDisabledPeopleClearsGdprData(): void
    {
        $people = new People();
        $people->setAlternateEmail('alternate@example.com');
        $people->addFile(new PeopleFile());

        $mobileAlternate = (new Phone())->setType(Phone::TYPE_MOBILE_ALTERNATE)->setNumber('+33600000000');
        $mobile = (new Phone())->setType(Phone::TYPE_MOBILE)->setNumber('+33600000001');
        $people->addPhone($mobileAlternate);
        $people->addPhone($mobile);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('persist')->with($people);
        $entityManager->expects($this->once())->method('flush');

        $repository = $this->getMockBuilder(PeopleRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getEntityManager'])
            ->getMock();
        $repository->method('getEntityManager')->willReturn($entityManager);

        $repository->disabledPeople($people);

        self::assertTrue($people->isDisabled());
        self::assertNull($people->getAlternateEmail());
        self::assertNull($people->getPhoto());
        self::assertCount(1, $people->getPhones());
        self::assertSame($mobile, $people->getPhones()->first());
    }
}
