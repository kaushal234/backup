<?php

declare(strict_types=1);

namespace App\Tests\Notifier\Service;

use App\Entity\Directory\Location;
use App\Entity\Directory\LocationContact;
use App\Entity\Directory\People;
use App\Entity\Directory\Position;
use App\Entity\EquipmentRecord;
use App\Entity\IndiceFactor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Factory\SanitizedEmailListFactory;
use App\Manager\Directory\TeamMemberManager;
use App\Notifier\Service\TechnicianOnCall\RecipientsFinder;
use App\Notifier\Service\TechnicianOnCall\TechnicianOnCallMailSubject;
use App\Notifier\UserSettingSubscriptionResolver;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Directory\PositionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\SecurityBundle\Security;

final class TechnicianOnCallRecipientsFinderTest extends TestCase
{
    use ProphecyTrait;

    /** @var PeopleRepository&MockObject */
    private PeopleRepository $peopleRepository;

    /** @var PositionRepository&MockObject */
    private PositionRepository $positionRepository;

    /** @var LocationRepository&MockObject */
    private LocationRepository $locationRepository;

    /** @var TeamMemberManager&MockObject */
    private TeamMemberManager $teamMemberManager;

    /** @var SanitizedEmailListFactory&MockObject */
    private SanitizedEmailListFactory $sanitizedEmailListFactory;

    /** @var SubscriptionRepository&MockObject */
    private SubscriptionRepository $subscriptionRepository;

    /** @var UserSettingSubscriptionResolver&MockObject */
    private UserSettingSubscriptionResolver $subscriptionResolver;

    private People $currentUser;

    private RecipientsFinder $recipientsFinder;

    protected function setUp(): void
    {
        $this->peopleRepository = $this->createMock(PeopleRepository::class);
        $this->positionRepository = $this->createMock(PositionRepository::class);
        $this->locationRepository = $this->createMock(LocationRepository::class);
        $this->teamMemberManager = $this->createMock(TeamMemberManager::class);
        $this->sanitizedEmailListFactory = $this->createMock(SanitizedEmailListFactory::class);
        $this->subscriptionRepository = $this->createMock(SubscriptionRepository::class);
        $this->subscriptionResolver = $this->createMock(UserSettingSubscriptionResolver::class);

        $this->currentUser = $this->prophesize(People::class)->reveal();

        $security = $this->createMock(Security::class);
        $security->method('getUser')->willReturn($this->currentUser);

        $this->recipientsFinder = new RecipientsFinder(
            $this->peopleRepository,
            $this->positionRepository,
            $this->locationRepository,
            $this->subscriptionResolver,
            $this->teamMemberManager,
            $this->sanitizedEmailListFactory,
            $this->subscriptionRepository,
            $security,
        );
    }

    public function testFindTosForTocCreatedByPeopleInternal(): void
    {
        $toc = new TechnicianOnCall();

        $assignee = $this->prophesize(People::class)->reveal();
        $technician = $this->prophesize(People::class)->reveal();
        $createdBy = $this->prophesize(People::class)->reveal();

        $toc->assignee = $assignee;
        $toc->technician = $technician;
        $toc->createdBy = $createdBy;
        $toc->indiceFactor = IndiceFactor::IF_1->value;

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with([$this->currentUser, $assignee, $technician, null, $createdBy, null])
            ->willReturn(['assignee@example.com', 'technician@example.com', 'creator@example.com']);

        $this->subscriptionRepository
            ->expects(self::once())
            ->method('findByResource')
            ->with($toc)
            ->willReturn([])
        ;

        $result = $this->recipientsFinder->findTos(
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL,
            $toc
        );

        self::assertSame(['assignee@example.com', 'technician@example.com', 'creator@example.com'], $result);
    }

    public function testFindCcsForTocCreatedByPeopleInternal(): void
    {
        $supervisor = $this->prophesize(People::class)->reveal();

        $assignee = $this->prophesize(People::class);
        $technician = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn($supervisor);

        $toc = $this->prophesize(TechnicianOnCall::class)->reveal();
        $toc->assignee = $assignee->reveal();
        $toc->technician = $technician->reveal();

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with([$supervisor])
            ->willReturn(['supervisor@example.com']);

        $result = $this->recipientsFinder->findCcs(
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL,
            $toc
        );

        self::assertSame(['supervisor@example.com'], $result);
    }

    public function testFindTosForTocCreatedByPeopleExternalUsesMainContactExtranetUser(): void
    {
        $tocProphecy = $this->prophesize(TechnicianOnCall::class);

        $mainContact = $this->prophesize(ExtranetUser::class)->reveal();
        $tocProphecy->getMainContact()->willReturn($mainContact);

        $toc = $tocProphecy->reveal();

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with([$mainContact])
            ->willReturn(['main.contact@example.com']);

        $result = $this->recipientsFinder->findTos(
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_EXTERNAL,
            $toc
        );

        self::assertSame(['main.contact@example.com'], $result);
    }

    public function testFindTosForTocUpdatedAddsRecipientsDependingOnIndiceFactorIf10(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_10->value;

        $supervisor = $this->prophesize(People::class)->reveal();

        $supervisorPosition = $this->prophesize(Position::class)->reveal();
        $assignee = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn($supervisor);
        $assignee->getPosition()->willReturn($supervisorPosition);
        $assignee->getId()->willReturn(10);

        $supervisor = $this->prophesize(People::class)->reveal();

        $toc->assignee = $assignee->reveal();

        $technician = $this->prophesize(People::class)->reveal();
        $toc->technician = $technician;

        $csmPosition = $this->prophesize(Position::class)->reveal();
        $this->positionRepository
            ->method('findOneBy')
            ->with(['code' => 'CSM'])
            ->willReturn($csmPosition);

        $this->teamMemberManager
            ->method('get')
            ->with([
                'user' => 10,
                'supervisor_position' => ['CSM'],
            ])
            ->willReturn([['id' => 42]]);

        $firstCsm = $this->prophesize(People::class)->reveal();
        $this->peopleRepository
            ->method('find')
            ->with(42)
            ->willReturn($firstCsm);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with([$this->currentUser, $assignee->reveal(), $technician, null, $supervisor, $firstCsm, null])
            ->willReturn(['assignee@example.com', 'technician@example.com', 'supervisor@example.com', 'csm@example.com']);

        $this->subscriptionRepository
            ->expects(self::once())
            ->method('findByResource')
            ->with($toc)
            ->willReturn([])
        ;

        $result = $this->recipientsFinder->findTos(
            TechnicianOnCallMailSubject::TOC_UPDATED,
            $toc
        );

        self::assertSame(['assignee@example.com', 'technician@example.com', 'supervisor@example.com', 'csm@example.com'], $result);
    }

    public function testFindTosForTocUpdatedAddsRecipientsDependingOnIndiceFactorIf100(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_100->value;

        $assigneeSupervisor = $this->prophesize(People::class)->reveal();
        $assignee = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn($assigneeSupervisor);
        $assignee->getId()->willReturn(10);
        $toc->assignee = $assignee->reveal();

        $technician = $this->prophesize(People::class)->reveal();
        $toc->technician = $technician;

        $csmPosition = $this->prophesize(Position::class)->reveal();
        $gcsdPosition = $this->prophesize(Position::class)->reveal();

        $this->positionRepository
            ->method('findOneBy')
            ->willReturnCallback(static function (array $criteria) use ($csmPosition, $gcsdPosition) {
                return match ($criteria['code'] ?? null) {
                    'CSM' => $csmPosition,
                    'GCSD' => $gcsdPosition,
                    default => null,
                };
            });

        $this->teamMemberManager
            ->method('get')
            ->with([
                'user' => 10,
                'supervisor_position' => ['CSM'],
            ])
            ->willReturn([['id' => 42]]);

        $firstCsm = $this->prophesize(User::class)->reveal();
        $this->peopleRepository
            ->method('find')
            ->with(42)
            ->willReturn($firstCsm);

        $gcsdMember = $this->prophesize(User::class)->reveal();
        $this->peopleRepository
            ->method('findBy')
            ->with(['position' => $gcsdPosition, 'disabled' => false])
            ->willReturn([$gcsdMember]);

        $salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $toc->salesOrganisationService = $salesOrganisationService;

        $manufacturerLocation = $this->prophesize(Location::class)->reveal();

        $equipmentRecord = $this->prophesize(EquipmentRecord::class);
        $equipmentRecord->getManufacturerLocation()->willReturn($manufacturerLocation);
        $toc->equipmentRecord = $equipmentRecord->reveal();

        $manufacturerMember = $this->prophesize(User::class)->reveal();
        $evpMember = $this->prophesize(User::class)->reveal();

        $this->peopleRepository
            ->method('findGroupsMembers')
            ->willReturnCallback(static function (array $roles, $locationOrService) use ($manufacturerLocation, $salesOrganisationService, $manufacturerMember, $evpMember) {
                if ($locationOrService === $manufacturerLocation && \in_array('ROLE_RME', $roles, true)) {
                    return [$manufacturerMember];
                }

                if ($locationOrService === $salesOrganisationService && $roles === ['ROLE_EVP']) {
                    return [$evpMember];
                }

                return [];
            });

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with([
                $this->currentUser,
                $assignee->reveal(),
                $technician,
                null,
                $assigneeSupervisor,
                $firstCsm,
                $manufacturerMember,
                $evpMember,
                $gcsdMember,
                null,
            ])
            ->willReturn([
                'assignee@example.com',
                'technician@example.com',
                'supervisor@example.com',
                'csm@example.com',
                'yoda@example.com',
                'evp@example.com',
                'gcsd@example.com',
            ])
        ;

        $this->subscriptionRepository
            ->expects(self::once())
            ->method('findByResource')
            ->with($toc)
            ->willReturn([])
        ;

        $result = $this->recipientsFinder->findTos(
            TechnicianOnCallMailSubject::TOC_UPDATED,
            $toc
        );

        self::assertSame([
            'assignee@example.com',
            'technician@example.com',
            'supervisor@example.com',
            'csm@example.com',
            'yoda@example.com',
            'evp@example.com',
            'gcsd@example.com',
        ], $result);
    }

    public function testFindTosForTocNewCommentInternalIncludesFfRecipientsWhenFactoryFlagIsActive(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1->value;
        $toc->factoryFlag = true;
        $toc->equipmentRecord = null;

        $assigneeSupervisor = $this->prophesize(People::class)->reveal();
        $assignee = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn($assigneeSupervisor);
        $toc->assignee = $assignee->reveal();

        $customer = $this->prophesize(Customer::class);
        $customer->getMainSalesRepresentative()->willReturn(null);
        $customer->getSecondarySalesRepresentatives()->willReturn(new ArrayCollection());
        $toc->customer = $customer->reveal();

        $salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $toc->salesOrganisationService = $salesOrganisationService;

        $evpMember = $this->prophesize(People::class)->reveal();
        $this->peopleRepository
            ->method('findGroupsMembers')
            ->with(['ROLE_EVP', 'CSM'], $salesOrganisationService)
            ->willReturn([$evpMember]);

        $this->subscriptionRepository
            ->method('findByResource')
            ->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(static fn (array $recipients) => \in_array($evpMember, $recipients, true)))
            ->willReturn(['evp@example.com']);

        $result = $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_NEW_COMMENT_INTERNAL, $toc);

        self::assertSame(['evp@example.com'], $result);
    }

    public function testFactoryRceoIsIncludedInIf1000Recipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1000->value;
        $toc->factoryFlag = false;
        $toc->technician = null;

        $assignee = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn(null);
        $toc->assignee = $assignee->reveal();

        $manufacturerLocation = $this->prophesize(Location::class)->reveal();
        $salesOrganisation = $this->prophesize(Location::class)->reveal();

        $equipmentRecord = $this->prophesize(EquipmentRecord::class);
        $equipmentRecord->getManufacturerLocation()->willReturn($manufacturerLocation);
        $equipmentRecord->getSalesOrganisation()->willReturn($salesOrganisation);
        $toc->equipmentRecord = $equipmentRecord->reveal();

        $salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $toc->salesOrganisationService = $salesOrganisationService;

        $alvest = $this->prophesize(Location::class)->reveal();
        $this->locationRepository->method('findOneBy')->with(['name' => 'ALVEST'])->willReturn($alvest);

        $rceoMember = $this->prophesize(People::class)->reveal();

        $this->peopleRepository
            ->method('findGroupsMembers')
            ->willReturnCallback(static function (array $roles, $location) use ($manufacturerLocation, $rceoMember) {
                if ($location === $manufacturerLocation && $roles === ['ROLE_RME', 'ROLE_EM', 'ROLE_PSM', 'ROLE_PSE', 'ROLE_COO', 'ROLE_RCEO', 'ROLE_CEO']) {
                    return [$rceoMember];
                }

                return [];
            });

        $this->positionRepository->method('findOneBy')->willReturn(null);
        $this->positionRepository->method('findBy')->willReturn([]);
        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(
                static fn (array $recipients) => \in_array($rceoMember, $recipients, true)
            ))
            ->willReturn(['rceo@example.com']);

        $result = $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_UPDATED, $toc);

        self::assertSame(['rceo@example.com'], $result);
    }

    public function testGchPositionMemberIsIncludedInIf1000Recipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1000->value;
        $toc->factoryFlag = false;
        $toc->technician = null;
        $toc->equipmentRecord = null;

        $assignee = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn(null);
        $toc->assignee = $assignee->reveal();

        $salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $toc->salesOrganisationService = $salesOrganisationService;

        $alvest = $this->prophesize(Location::class)->reveal();
        $this->locationRepository->method('findOneBy')->with(['name' => 'ALVEST'])->willReturn($alvest);

        $this->peopleRepository->method('findGroupsMembers')->willReturn([]);

        $gchPosition = $this->prophesize(Position::class)->reveal();
        $this->positionRepository->method('findOneBy')->willReturn(null);
        $this->positionRepository
            ->method('findBy')
            ->with(['code' => ['GCH', 'GCTO', 'TCEO']])
            ->willReturn([$gchPosition]);

        $gchMember = $this->prophesize(People::class)->reveal();
        $this->peopleRepository
            ->method('findBy')
            ->with(['position' => $gchPosition, 'disabled' => false])
            ->willReturn([$gchMember]);

        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(
                static fn (array $recipients) => \in_array($gchMember, $recipients, true)
            ))
            ->willReturn(['gch@example.com']);

        $result = $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_UPDATED, $toc);

        self::assertSame(['gch@example.com'], $result);
    }

    public function testUserWithRoleGchButNotGchPositionIsNotIncludedInIf1000Recipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1000->value;
        $toc->factoryFlag = false;
        $toc->technician = null;
        $toc->equipmentRecord = null;

        $assignee = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn(null);
        $toc->assignee = $assignee->reveal();

        $salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $toc->salesOrganisationService = $salesOrganisationService;

        $alvest = $this->prophesize(Location::class)->reveal();
        $this->locationRepository->method('findOneBy')->with(['name' => 'ALVEST'])->willReturn($alvest);

        // This member only has the ROLE_GCH role, not the GCH position.
        $roleGchMember = $this->prophesize(People::class)->reveal();
        $this->peopleRepository
            ->method('findGroupsMembers')
            ->willReturnCallback(static function (array $roles) use ($roleGchMember) {
                if (\in_array('ROLE_GCH', $roles, true)) {
                    return [$roleGchMember];
                }

                return [];
            });

        $this->positionRepository->method('findOneBy')->willReturn(null);
        $this->positionRepository
            ->method('findBy')
            ->with(['code' => ['GCH', 'GCTO', 'TCEO']])
            ->willReturn([]);

        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(
                static fn (array $recipients) => !\in_array($roleGchMember, $recipients, true)
            ))
            ->willReturn([]);

        $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_UPDATED, $toc);
    }

    public function testMainAsmFromCustomerHierarchyIsAddedToIf100Recipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_100->value;

        $customer = $this->prophesize(Customer::class)->reveal();
        $toc->customer = $customer;

        $hierarchyAsm = $this->prophesize(People::class)->reveal();

        $this->peopleRepository
            ->expects(self::once())
            ->method('findAllMainAsmForCustomerHierarchy')
            ->with($customer)
            ->willReturn([$hierarchyAsm]);

        $this->peopleRepository->method('findGroupsMembers')->willReturn([]);
        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(static fn (array $recipients) => \in_array($hierarchyAsm, $recipients, true)))
            ->willReturn(['hierarchy-asm@example.com']);

        $result = $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_UPDATED, $toc);

        self::assertSame(['hierarchy-asm@example.com'], $result);
    }

    public function testSecondaryAsmFromCustomerHierarchyIsAddedToIf1000Recipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1000->value;

        $customer = $this->prophesize(Customer::class)->reveal();
        $toc->customer = $customer;

        $secondaryAsm = $this->prophesize(People::class)->reveal();

        $this->peopleRepository->method('findAllMainAsmForCustomerHierarchy')->with($customer)->willReturn([]);
        $this->peopleRepository
            ->expects(self::once())
            ->method('findAllSecondaryAsmsForCustomerHierarchy')
            ->with($customer)
            ->willReturn([$secondaryAsm]);

        $this->peopleRepository->method('findGroupsMembers')->willReturn([]);
        $this->positionRepository->method('findOneBy')->willReturn(null);
        $this->positionRepository->method('findBy')->willReturn([]);
        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(static fn (array $recipients) => \in_array($secondaryAsm, $recipients, true)))
            ->willReturn(['secondary-asm@example.com']);

        $result = $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_UPDATED, $toc);

        self::assertSame(['secondary-asm@example.com'], $result);
    }

    public function testSecondaryAsmFromCustomerHierarchyIsNotAddedToIf100Recipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_100->value;
        $toc->customer = $this->prophesize(Customer::class)->reveal();

        $this->peopleRepository->method('findAllMainAsmForCustomerHierarchy')->willReturn([]);
        $this->peopleRepository->expects(self::never())->method('findAllSecondaryAsmsForCustomerHierarchy');

        $this->peopleRepository->method('findGroupsMembers')->willReturn([]);
        $this->subscriptionRepository->method('findByResource')->willReturn([]);
        $this->sanitizedEmailListFactory->method('buildCleanEmailAddressList')->willReturn([]);

        $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_UPDATED, $toc);
    }

    public function testCustomerHierarchyAsmsAreNotFetchedWhenIndiceFactorIsBelowIf100(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_10->value;
        $toc->customer = $this->prophesize(Customer::class)->reveal();

        $this->peopleRepository->expects(self::never())->method('findAllMainAsmForCustomerHierarchy');
        $this->peopleRepository->expects(self::never())->method('findAllSecondaryAsmsForCustomerHierarchy');

        $this->subscriptionRepository->method('findByResource')->willReturn([]);
        $this->sanitizedEmailListFactory->method('buildCleanEmailAddressList')->willReturn([]);

        $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_UPDATED, $toc);
    }

    public function testCreatedByExtranetUserIsExcludedFromTocCreatedByPeopleInternalRecipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1->value;
        $toc->createdBy = $this->prophesize(ExtranetUser::class)->reveal();

        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(
                static fn (array $recipients) => !\in_array($toc->createdBy, $recipients, true)
            ))
            ->willReturn([]);

        $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL, $toc);
    }

    public static function internalCommentSubjectsProvider(): iterable
    {
        yield 'TOC_NEW_COMMENT_INTERNAL' => [TechnicianOnCallMailSubject::TOC_NEW_COMMENT_INTERNAL];
        yield 'TOC_NEW_COMMENT_BY_CUST' => [TechnicianOnCallMailSubject::TOC_NEW_COMMENT_BY_CUST];
        yield 'TOC_PENDING_TO_IN_PROGRESS_INTERNAL' => [TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_INTERNAL];
    }

    /**
     * @dataProvider internalCommentSubjectsProvider
     */
    public function testCreatedByExtranetUserIsExcludedFromInternalCommentRecipients(TechnicianOnCallMailSubject $subject): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1->value;
        $toc->factoryFlag = false;
        $toc->createdBy = $this->prophesize(ExtranetUser::class)->reveal();

        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(
                static fn (array $recipients) => !\in_array($toc->createdBy, $recipients, true)
            ))
            ->willReturn([]);

        $this->recipientsFinder->findTos($subject, $toc);
    }

    public function testCreatedByExtranetUserIsExcludedFromTocFactoryFlagRecipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->equipmentRecord = null;
        $toc->createdBy = $this->prophesize(ExtranetUser::class)->reveal();

        $assigneeSupervisor = $this->prophesize(People::class)->reveal();
        $assignee = $this->prophesize(People::class);
        $assignee->getSupervisor()->willReturn($assigneeSupervisor);
        $toc->assignee = $assignee->reveal();

        $customer = $this->prophesize(Customer::class);
        $customer->getMainSalesRepresentative()->willReturn(null);
        $customer->getSecondarySalesRepresentatives()->willReturn(new ArrayCollection());
        $toc->customer = $customer->reveal();

        $salesOrganisationService = $this->prophesize(Location::class)->reveal();
        $toc->salesOrganisationService = $salesOrganisationService;

        $this->peopleRepository->method('findGroupsMembers')->willReturn([]);
        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(
                static fn (array $recipients) => !\in_array($toc->createdBy, $recipients, true)
            ))
            ->willReturn([]);

        $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_FACTORY_FLAG, $toc);
    }

    public function testCreatedByExtranetUserRemainsInTocCreatedByCustomerRecipients(): void
    {
        $toc = new TechnicianOnCall();
        $toc->indiceFactor = IndiceFactor::IF_1->value;
        $toc->equipmentRecord = null;
        $toc->createdBy = $this->prophesize(ExtranetUser::class)->reveal();

        $locationContact = (new LocationContact())->setServiceHubEmail('hub@example.com');
        $salesOrganisationService = $this->prophesize(Location::class);
        $salesOrganisationService->getContact()->willReturn($locationContact);
        $toc->salesOrganisationService = $salesOrganisationService->reveal();

        $this->subscriptionRepository->method('findByResource')->willReturn([]);

        $this->sanitizedEmailListFactory
            ->expects(self::once())
            ->method('buildCleanEmailAddressList')
            ->with(self::callback(
                static fn (array $recipients) => \in_array($toc->createdBy, $recipients, true)
            ))
            ->willReturn(['extranet@example.com']);

        $result = $this->recipientsFinder->findTos(TechnicianOnCallMailSubject::TOC_CREATED_BY_CUSTOMER, $toc);

        self::assertSame(['extranet@example.com'], $result);
    }
}
