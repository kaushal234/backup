<?php

declare(strict_types=1);

namespace App\Tests\Notifier\Legal;

use App\Entity\Directory\BusinessUnit;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\Directory\Region;
use App\Entity\Directory\SubDivision;
use App\Entity\Legal\Category;
use App\Entity\Legal\Contract;
use App\Entity\Legal\SubCategory;
use App\Entity\Sales\Customer;
use App\Entity\Sales\MainSalesRepresentative;
use App\Entity\Sales\SecondarySalesRepresentative;
use App\Notifier\Legal\RecipientFinder;
use App\Repository\Directory\PeopleRepository;
use PHPUnit\Framework\TestCase;

class RecipientFinderTest extends TestCase
{
    public function testCustomerRecipientsOnlyIncludesSalesRepsMatchingContractBusinessUnits(): void
    {
        $subDivisionA = $this->createSubDivision(1);
        $subDivisionB = $this->createSubDivision(2);

        $mainAsm = new People();
        $secondaryAsm = new People();

        $customer = new Customer();
        $customer->setMainSalesRepresentative($this->createSalesRep(MainSalesRepresentative::class, $mainAsm, $subDivisionA));
        $customer->addSecondarySalesRepresentative($this->createSalesRep(SecondarySalesRepresentative::class, $secondaryAsm, $subDivisionB));

        // Contract only targets a business unit in sub-division A → only the main rep must be notified.
        $contract = $this->createContract(
            Category::CUSTOMERS,
            customers: [$customer],
            businessUnits: [$this->createBusinessUnit(new Location(), $subDivisionA)],
        );

        $recipients = (new RecipientFinder($this->createMock(PeopleRepository::class)))->findTos($contract);

        self::assertSame([$contract->owner, $mainAsm], $recipients);
    }

    public function testCustomerWithoutBusinessUnitNotifiesNoSalesRep(): void
    {
        $customer = new Customer();
        $customer->setMainSalesRepresentative($this->createSalesRep(MainSalesRepresentative::class, new People(), $this->createSubDivision(1)));

        $contract = $this->createContract(Category::CUSTOMERS, customers: [$customer]);

        $recipients = (new RecipientFinder($this->createMock(PeopleRepository::class)))->findTos($contract);

        self::assertSame([$contract->owner], $recipients);
    }

    public function testRealEstateRecipientsAreGroupMembersOfBusinessUnitLocations(): void
    {
        $location = new Location();
        $ceo = new People();

        $repository = $this->createMock(PeopleRepository::class);
        $repository->expects(self::once())
            ->method('findGroupsMembers')
            ->with(['ROLE_CEO', 'ROLE_COO'], $location)
            ->willReturn([$ceo]);

        $contract = $this->createContract(Category::REAL_ESTATE, businessUnits: [$this->createBusinessUnit($location)]);

        $recipients = (new RecipientFinder($repository))->findTos($contract);

        self::assertSame([$contract->owner, $ceo], $recipients);
    }

    public function testVendorsAgreementsRecipientsCombineLocationMlmAndGlobalCpo(): void
    {
        $location = new Location();
        $mlm = new People();
        $cpo = new People();

        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('findGroupsMembers')
            ->willReturnCallback(static fn (array $groups, ?Location $given = null): array => match (true) {
                ['ROLE_MLM'] === $groups && $given === $location => [$mlm],
                ['ROLE_CPO'] === $groups && null === $given => [$cpo],
                default => [],
            });

        $contract = $this->createContract(Category::VENDORS, businessUnits: [$this->createBusinessUnit($location)]);

        $recipients = (new RecipientFinder($repository))->findTos($contract);

        self::assertSame([$contract->owner, $mlm, $cpo], $recipients);
    }

    public function testBankingRecipientsCombineLocationCfoAndGlobalGcfo(): void
    {
        $location = new Location();
        $cfo = new People();
        $gcfo = new People();

        $repository = $this->createMock(PeopleRepository::class);
        $repository->method('findGroupsMembers')
            ->willReturnCallback(static fn (array $groups, ?Location $given = null): array => match (true) {
                ['ROLE_CFO'] === $groups && $given === $location => [$cfo],
                ['ROLE_GCFO'] === $groups && null === $given => [$gcfo],
                default => [],
            });

        $contract = $this->createContract(Category::BANK, businessUnits: [$this->createBusinessUnit($location)]);

        $recipients = (new RecipientFinder($repository))->findTos($contract);

        self::assertSame([$contract->owner, $cfo, $gcfo], $recipients);
    }

    public function testItRecipientsAreGlobalGroupMembers(): void
    {
        $cio = new People();

        $repository = $this->createMock(PeopleRepository::class);
        $repository->expects(self::once())
            ->method('findGroupsMembers')
            ->with(['ROLE_CIO', 'ROLE_LGS'])
            ->willReturn([$cio]);

        $contract = $this->createContract(Category::IP_IT);

        $recipients = (new RecipientFinder($repository))->findTos($contract);

        self::assertSame([$contract->owner, $cio], $recipients);
    }

    public function testUnknownCategoryReturnsOnlyOwner(): void
    {
        $repository = $this->createMock(PeopleRepository::class);
        $repository->expects(self::never())->method('findGroupsMembers');

        $contract = $this->createContract('UNKNOWN');

        $recipients = (new RecipientFinder($repository))->findTos($contract);

        self::assertSame([$contract->owner], $recipients);
    }

    public function testFindCcsReturnsOwnerSupervisorWhenPresent(): void
    {
        $supervisor = new People();
        $contract = $this->createContract(Category::CUSTOMERS);
        $contract->owner->setSupervisor($supervisor);

        $recipients = (new RecipientFinder($this->createMock(PeopleRepository::class)))->findCcs($contract);

        self::assertSame([$supervisor], $recipients);
    }

    public function testFindCcsReturnsEmptyWhenOwnerHasNoSupervisor(): void
    {
        $contract = $this->createContract(Category::CUSTOMERS);

        $recipients = (new RecipientFinder($this->createMock(PeopleRepository::class)))->findCcs($contract);

        self::assertSame([], $recipients);
    }

    private function createSubDivision(int $id): SubDivision
    {
        $subDivision = new SubDivision();
        $subDivision->name = \sprintf('Sub %d', $id);

        $reflection = new \ReflectionProperty(SubDivision::class, 'id');
        $reflection->setValue($subDivision, $id);

        return $subDivision;
    }

    /**
     * @template T of MainSalesRepresentative|SecondarySalesRepresentative
     *
     * @param class-string<T> $class
     *
     * @return T
     */
    private function createSalesRep(string $class, People $asm, SubDivision $subDivision): MainSalesRepresentative|SecondarySalesRepresentative
    {
        $representative = new $class();
        $representative->asm = $asm;
        $representative->subDivision = $subDivision;

        return $representative;
    }

    private function createBusinessUnit(Location $location, ?SubDivision $subDivision = null): BusinessUnit
    {
        $region = null;
        if (null !== $subDivision) {
            $region = $this->createMock(Region::class);
            $region->method('getSubDivision')->willReturn($subDivision);
        }

        $businessUnit = $this->createMock(BusinessUnit::class);
        $businessUnit->method('getLocation')->willReturn($location);
        $businessUnit->method('getRegion')->willReturn($region);

        return $businessUnit;
    }

    /**
     * @param list<Customer>     $customers
     * @param list<BusinessUnit> $businessUnits
     */
    private function createContract(
        string $categoryName,
        array $customers = [],
        array $businessUnits = [],
    ): Contract {
        $category = new Category();
        $category->name = $categoryName;

        $subCategory = new SubCategory();
        $subCategory->category = $category;

        $contract = new Contract();
        $contract->subCategory = $subCategory;
        $contract->owner = new People();

        foreach ($customers as $customer) {
            $contract->addCustomer($customer);
        }

        foreach ($businessUnits as $businessUnit) {
            $contract->addBusinessUnit($businessUnit);
        }

        return $contract;
    }
}
