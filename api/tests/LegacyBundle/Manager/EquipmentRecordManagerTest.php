<?php

declare(strict_types=1);

namespace Tests\LegacyBundle\Manager;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Sales\Customer;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\EquipmentRecordManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EquipmentRecordManagerTest extends TestCase
{
    public function testTransferEquipmentRecordsWithResults()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        /** @var EntityManagerInterface|MockObject $entityManagerMock */
        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var IriConverterInterface|MockObject $iriConverterMock */
        $iriConverterMock = $this->getMockBuilder(IriConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sourceCustomer = (new Customer())->setLegacyId(42);
        $targetCustomer = (new Customer())->setLegacyId(69);

        $expectedUpdateRequests = [
            ['UPDATE service SET buyer_customer_id = :target_customer_id WHERE buyer_customer_id = :source_customer_id', ['target_customer_id' => 69, 'source_customer_id' => 42]],
            ['UPDATE service SET customer_id = :target_customer_id WHERE customer_id = :source_customer_id', ['target_customer_id' => 69, 'source_customer_id' => 42]],
        ];

        $expectedSelectRequests = [
            ['SELECT * FROM service WHERE buyer_customer_id = :customer', ['customer' => 42]],
            ['SELECT * FROM service WHERE customer_id = :customer', ['customer' => 42]],
        ];

        $connection
            ->expects(self::exactly(2))
            ->method('executeStatement')
            ->withConsecutive(...$expectedUpdateRequests)
        ;

        $connection
            ->expects(self::exactly(2))
            ->method('fetchAllAssociative')
            ->withConsecutive(...$expectedSelectRequests)
            ->willReturnOnConsecutiveCalls([['sn' => 'T69695']], [['sn' => 'T69696']])
        ;

        $iriConverterMock
            ->expects(self::once())
            ->method('getIriFromResource')
            ->with($sourceCustomer)
            ->willReturn('/sales/customers/42')
        ;

        $entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with((new Comment())->setMessage('Equipment Records #T69695, #T69696 updated.')->setResource('/sales/customers/42'))
        ;

        $manager = new EquipmentRecordManager($connection, $entityManagerMock, $iriConverterMock);

        $manager->transferCustomer($sourceCustomer, $targetCustomer);
    }

    public function testTransferEquipmentRecordsWithNoResult()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        $sourceCustomer = (new Customer())->setLegacyId(42);
        $targetCustomer = (new Customer())->setLegacyId(69);

        /** @var EntityManagerInterface|MockObject $entityManagerMock */
        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var IriConverterInterface|MockObject $iriConverterMock */
        $iriConverterMock = $this->getMockBuilder(IriConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedRequests = [
            ['SELECT * FROM service WHERE buyer_customer_id = :customer', ['customer' => 42]],
            ['SELECT * FROM service WHERE customer_id = :customer', ['customer' => 42]],
        ];

        $connection
            ->expects(self::exactly(2))
            ->method('fetchAllAssociative')
            ->withConsecutive(...$expectedRequests)
            ->willReturn([])
        ;

        $iriConverterMock
            ->expects(self::once())
            ->method('getIriFromResource')
            ->with($sourceCustomer)
            ->willReturn('/sales/customers/42')
        ;

        $entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with((new Comment())->setMessage('No equipment record updated.')->setResource('/sales/customers/42'));

        $manager = new EquipmentRecordManager($connection, $entityManagerMock, $iriConverterMock);

        $manager->transferCustomer($sourceCustomer, $targetCustomer);
    }

    public function testTransferEquipmentRecordsWithPartialResults()
    {
        /** @var Connection|MockObject $connection */
        $connection = $this->getConnection();

        /** @var EntityManagerInterface|MockObject $entityManagerMock */
        $entityManagerMock = $this->getMockBuilder(EntityManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var IriConverterInterface|MockObject $iriConverterMock */
        $iriConverterMock = $this->getMockBuilder(IriConverterInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $sourceCustomer = (new Customer())->setLegacyId(42);
        $targetCustomer = (new Customer())->setLegacyId(69);

        $expectedRequests = [
            ['SELECT * FROM service WHERE buyer_customer_id = :customer', ['customer' => 42]],
            ['SELECT * FROM service WHERE customer_id = :customer', ['customer' => 42]],
        ];

        $connection
            ->expects(self::exactly(2))
            ->method('fetchAllAssociative')
            ->withConsecutive(...$expectedRequests)
            ->willReturnOnConsecutiveCalls([], [['sn' => 'T69696']]);

        $connection
            ->expects(self::exactly(1))
            ->method('executeStatement')
            ->with('UPDATE service SET customer_id = :target_customer_id WHERE customer_id = :source_customer_id', ['target_customer_id' => 69, 'source_customer_id' => 42])
            ->willReturnOnConsecutiveCalls(0, 1);

        $iriConverterMock
            ->expects(self::once())
            ->method('getIriFromResource')
            ->with($sourceCustomer)
            ->willReturn('/sales/customers/42')
        ;

        $entityManagerMock
            ->expects(self::once())
            ->method('persist')
            ->with((new Comment())->setMessage('Equipment Records #T69696 updated.')->setResource('/sales/customers/42'));

        $manager = new EquipmentRecordManager($connection, $entityManagerMock, $iriConverterMock);

        $manager->transferCustomer($sourceCustomer, $targetCustomer);
    }

    private function getConnection(): MockObject
    {
        /** @var MockObject|Connection $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $connection
            ->method('createQueryBuilder')
            ->willReturn(new QueryBuilder($connection));

        return $connection;
    }
}
