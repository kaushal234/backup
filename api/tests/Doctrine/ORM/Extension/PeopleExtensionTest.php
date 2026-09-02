<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\ORM\Extension\PeopleExtension;
use App\Entity\AuthorizedApplication;
use App\Entity\Directory\People;
use App\Entity\Purchasing\VendorUser;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartner;
use App\ION\Resources\MasterData\BusinessPartners\BusinessPartnerContact;
use App\ION\Resources\MasterData\EnterpriseModel\Employee;
use App\ION\Resources\MasterData\EnterpriseModel\Entities\Department;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Psr\Container\ContainerInterface;
use Symfony\Bundle\SecurityBundle\Security;

class PeopleExtensionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideUserAndOperation
     */
    public function testPeopleAreFilteredForTheItem(string $resourceClass, object $user, Operation $operation)
    {
        $securityProphecy = $this->prophesize(Security::class);

        $validResourceAndOperation = People::class === $resourceClass && 'buyers' === $operation->getName();

        $containerProphecy = $this->prophesize(ContainerInterface::class);
        $containerProphecy->get(Security::class)->shouldBeCalledTimes((int) $validResourceAndOperation)->willReturn($securityProphecy->reveal());

        $securityProphecy->getUser()->shouldBeCalledTimes((int) $validResourceAndOperation)->willReturn($user);

        $emProphecy = $this->prophesize(EntityManagerInterface::class);
        $emProphecy->getExpressionBuilder()->willReturn(new Expr());
        $queryBuilder = new QueryBuilder($emProphecy->reveal());

        $queryBuilder->from(People::class, 'o');

        $extension = new PeopleExtension($containerProphecy->reveal());

        $extension->applyToCollection($queryBuilder, new QueryNameGenerator(), $resourceClass, $operation);

        /** @var Andx|null $where */
        $where = $queryBuilder->getDQLPart('where');

        if (!$user instanceof VendorUser || !$validResourceAndOperation) {
            self::assertNull($where);

            return;
        }

        self::assertInstanceOf(Andx::class, $where);

        $parts = $where->getParts();

        self::assertCount(1, $parts);
        self::assertInstanceOf(Func::class, $parts[0]);

        /** @var Func $condition */
        $condition = $parts[0];
        self::assertSame('o.email IN', $condition->getName());
        self::assertSame([':buyers'], $condition->getArguments());

        self::assertCount(1, $queryBuilder->getParameters());
        self::assertNotNull($queryBuilder->getParameter('buyers'));
        self::assertSame(['b@ille.ur', 'br@ille.ur'], $queryBuilder->getParameter('buyers')->getValue());
    }

    public function provideUserAndOperation()
    {
        yield 'wrong resource class and authorized app on the correct operation' => [\stdClass::class, new AuthorizedApplication(), new GetCollection(name: 'buyers')];
        yield 'authorized app on the correct operation' => [People::class, new AuthorizedApplication(), new GetCollection(name: 'buyers')];
        yield 'authorized app on the wrong operation' => [People::class, new AuthorizedApplication(), new GetCollection(name: 'pouet')];
        yield 'wrong resource class and people on the correct operation' => [\stdClass::class, new People(), new GetCollection(name: 'buyers')];
        yield 'people on the correct operation' => [People::class, new People(), new GetCollection(name: 'buyers')];
        yield 'people on the wrong operation' => [People::class, new People(), new GetCollection(name: 'pouet')];

        $vendorUser = new VendorUser();
        $vendorUser->contact = new BusinessPartnerContact();
        $businessPartner = new BusinessPartner();
        $department = new Department();
        $department->buyer = new Employee();
        $department->buyer->emailAddress = 'b@ille.ur';
        $businessPartner->addBuyFromDepartment($department);
        $department2 = new Department();
        $department2->buyer = new Employee();
        $department2->buyer->emailAddress = 'br@ille.ur';
        $businessPartner->addBuyFromDepartment($department2);
        $vendorUser->contact->addBusinessPartner($businessPartner);

        yield 'wrong resource class and vendor user on the correct operation' => [\stdClass::class, $vendorUser, new GetCollection(name: 'buyers')];
        yield 'vendor user on the correct operation' => [People::class, $vendorUser, new GetCollection(name: 'buyers')];
        yield 'vendor user on the wrong operation' => [People::class, $vendorUser, new GetCollection(name: 'pouet')];
    }
}
