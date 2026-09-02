<?php

declare(strict_types=1);

namespace App\Tests\Filter\Support;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use App\Entity\Support\EquipmentSerial;
use App\Filter\Support\EquipmentSerialSchematicsFilter;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;

class EquipmentSerialSchematicsFilterTest extends KernelTestCase
{
    use ProphecyTrait;
    private ?EntityManager $entityManager = null;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()->get('doctrine')->getManager('default');
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        $this->entityManager = null;

        parent::tearDown();
    }

    public function testApply()
    {
        $queryBuilder = $this->entityManager->getRepository(EquipmentSerial::class)->createQueryBuilder('es');
        $queryNameGeneratorInterfaceProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $filter = new EquipmentSerialSchematicsFilter();

        $request = new Request();
        $request->query->set(EquipmentSerialSchematicsFilter::FILTER_NAME, true);

        $filter->apply($queryBuilder, $queryNameGeneratorInterfaceProphecy->reveal(), EquipmentSerial::class, null, [
            'request' => $request,
        ]);

        $result = $queryBuilder->getQuery()->getResult();

        self::assertCount(4, $result);
        self::assertSame('SCHEM, BRAKING', $result[0]->component->name);
    }

    public function testNoRequest()
    {
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderProphecy->expr()->shouldNotBeCalled();

        $queryNameGeneratorInterfaceProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $filter = new EquipmentSerialSchematicsFilter();
        $filter->apply($queryBuilderProphecy->reveal(), $queryNameGeneratorInterfaceProphecy->reveal(), EquipmentSerial::class);
    }

    public function testNoFilter()
    {
        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderProphecy->expr()->shouldNotBeCalled();

        $queryNameGeneratorInterfaceProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $filter = new EquipmentSerialSchematicsFilter();
        $filter->apply($queryBuilderProphecy->reveal(), $queryNameGeneratorInterfaceProphecy->reveal(), EquipmentSerial::class, null, ['request' => new Request()]);
    }

    public function testFilterHasFalse()
    {
        $queryBuilder = $this->entityManager->getRepository(EquipmentSerial::class)->createQueryBuilder('es');
        $queryNameGeneratorInterfaceProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $filter = new EquipmentSerialSchematicsFilter();

        $request = new Request();
        $request->query->set(EquipmentSerialSchematicsFilter::FILTER_NAME, false);

        $filter->apply($queryBuilder, $queryNameGeneratorInterfaceProphecy->reveal(), EquipmentSerial::class, null, [
            'request' => $request,
        ]);

        $result = $queryBuilder->getQuery()->getResult();

        // schematics=false returns the complement of testApply (all non-schematic serials,
        // including the serial without a component).
        self::assertCount(12, $result);
        self::assertContains(null, array_map(static fn (EquipmentSerial $serial) => $serial->component, $result));
    }

    public function testExceptionForWrongClass()
    {
        $this->expectException(\Exception::class);

        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderProphecy->expr()->shouldNotBeCalled();

        $queryNameGeneratorInterfaceProphecy = $this->prophesize(QueryNameGeneratorInterface::class);

        $request = new Request();
        $request->query->set(EquipmentSerialSchematicsFilter::FILTER_NAME, true);

        $filter = new EquipmentSerialSchematicsFilter();
        $filter->apply($queryBuilderProphecy->reveal(), $queryNameGeneratorInterfaceProphecy->reveal(), \stdClass::class, null, ['request' => $request]);
    }

    public function testDescription()
    {
        $filter = new EquipmentSerialSchematicsFilter();
        $expected = [
            EquipmentSerialSchematicsFilter::FILTER_NAME => [
                'property' => EquipmentSerialSchematicsFilter::FILTER_NAME,
                'type' => 'bool',
                'required' => false,
            ],
        ];

        self::assertSame($expected, $filter->getDescription(\stdClass::class));
    }
}
