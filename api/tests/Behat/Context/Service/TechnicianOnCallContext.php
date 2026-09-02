<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Common\Airport;
use App\Entity\Directory\Location;
use App\Entity\Directory\People;
use App\Entity\EquipmentRecord;
use App\Entity\IndiceFactor;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use App\Entity\Service\CustomerServiceRecord\Intervention;
use App\Entity\Service\CustomerServiceRecord\TechnicianOnCallCustomerServiceRecord;
use App\Entity\Service\ServiceActivity;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\BacklogReport;
use App\Entity\Service\TechnicianOnCallType;
use App\Entity\Support\UnitOperationalStatus;
use Behat\Behat\Context\Context;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Doctrine\ORM\EntityManagerInterface;

class TechnicianOnCallContext implements Context
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
    }

    /**
     * @Then I load Technician On Call Operate data
     */
    public function ILoadOperateData()
    {
        $tocWithOneIntervention = $this->createTechnicianOnCall(solvedAt: 'now', title: 'TOC with 1 intervention', description: 'TOC with 1 intervention', status: TechnicianOnCall::SOLVED);
        $csrForTocWithOneIntervention = $this->createCustomerServiceRecord(technicianOnCall: $tocWithOneIntervention, status: AbstractCustomerServiceRecord::COMPLETED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithOneIntervention, status: Intervention::SOLVED);

        $tocWithTwoIntervention = $this->createTechnicianOnCall(solvedAt: 'now', title: 'TOC with 2 intervention', description: 'TOC with 2 intervention', status: TechnicianOnCall::SOLVED);
        $csrForTocWithTwoIntervention = $this->createCustomerServiceRecord(technicianOnCall: $tocWithTwoIntervention, status: AbstractCustomerServiceRecord::COMPLETED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithTwoIntervention, status: Intervention::SOLVED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithTwoIntervention, status: Intervention::SOLVED);

        $tocWithThreeIntervention = $this->createTechnicianOnCall(solvedAt: 'now', title: 'TOC with 3 intervention', description: 'TOC with 3 intervention', status: TechnicianOnCall::SOLVED);
        $csrForTocWithThreeIntervention = $this->createCustomerServiceRecord(technicianOnCall: $tocWithThreeIntervention, status: AbstractCustomerServiceRecord::COMPLETED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithThreeIntervention, status: Intervention::SOLVED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithThreeIntervention, status: Intervention::SOLVED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithThreeIntervention, status: Intervention::SOLVED);

        $tocWithTwoCustomerServiceRecord = $this->createTechnicianOnCall(solvedAt: 'now', title: 'TOC with 2 CSR', description: 'TOC with 2 CSR', status: TechnicianOnCall::SOLVED);
        $csrForTocWithThreeCSROne = $this->createCustomerServiceRecord(technicianOnCall: $tocWithTwoCustomerServiceRecord, status: AbstractCustomerServiceRecord::COMPLETED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithThreeCSROne, status: Intervention::SOLVED);
        $csrForTocWithThreeCSRTwo = $this->createCustomerServiceRecord(technicianOnCall: $tocWithTwoCustomerServiceRecord, status: AbstractCustomerServiceRecord::COMPLETED);
        $this->createIntervention(customerServiceRecord: $csrForTocWithThreeCSRTwo, status: Intervention::SOLVED);

        $this->entityManager->flush();
        sleep(1);
    }

    /**
     * @Then I generate Technician On Call backlog data
     */
    public function IGenerateBacklogData()
    {
        $technicianOnCallRepository = $this->entityManager->getRepository(TechnicianOnCall::class);
        $locationRepository = $this->entityManager->getRepository(Location::class);

        $locations = $locationRepository->findBy(['capability.sso' => true]);
        $technicianOnCalls = new ArrayCollection($technicianOnCallRepository->findAll());

        $criteria = Criteria::create()
            ->orderBy(['createdAt' => Order::Ascending])
            ->setMaxResults(1)
        ;

        /** @var TechnicianOnCall $oldest */
        $oldest = $technicianOnCalls->matching($criteria)->first();
        $date = $oldest->createdAt;
        $date = (clone $date)->modify('last day of previous month');

        do {
            $firstDay = (new \DateTimeImmutable($date->format('Y-m-01')))->setTime(0, 0);
            $lastDay = (new \DateTimeImmutable($date->format('Y-m-d')))->setTime(23, 59, 59);

            $criteria = Criteria::create()
                ->where(Criteria::expr()->andX(
                    Criteria::expr()->gte('createdAt', $firstDay),
                    Criteria::expr()->lte('createdAt', $lastDay)
                ))
            ;

            $monthlyTechnicianOnCalls = $technicianOnCalls->matching($criteria);

            $backlogs = new ArrayCollection();

            /** @var TechnicianOnCall $monthlyTechnicianOnCall */
            foreach ($monthlyTechnicianOnCalls as $monthlyTechnicianOnCall) {
                $locationCriteria = Criteria::create()
                    ->where(Criteria::expr()->eq('salesOrganisation', $monthlyTechnicianOnCall->salesOrganisationService))
                ;

                $backlog = $backlogs->matching($locationCriteria)->first();

                if (!$backlog) {
                    $backlog = new BacklogReport();
                    $backlog->date = $lastDay;
                    $backlog->salesOrganisation = $monthlyTechnicianOnCall->salesOrganisationService;

                    $backlogs->add($backlog);
                }

                $this->entityManager->persist($backlog);

                $solvedAt = $monthlyTechnicianOnCall->solvedAt;

                if (null !== $solvedAt && $solvedAt <= $date) {
                    continue;
                }

                $backlog->addTechnicianOnCall($monthlyTechnicianOnCall);

                $this->entityManager->persist($backlog);
            }

            foreach ($locations as $location) {
                $locationCriteria = Criteria::create()
                    ->where(Criteria::expr()->eq('salesOrganisation', $location))
                ;

                if (!$backlogs->matching($locationCriteria)->isEmpty()) {
                    continue;
                }

                $backlog = new BacklogReport();
                $backlog->date = $lastDay;
                $backlog->salesOrganisation = $location;

                $backlogs->add($backlog);
                $this->entityManager->persist($backlog);
            }

            $date = $date->modify('last day of next month');
        } while ($date <= new \DateTimeImmutable());

        $this->entityManager->flush();
        sleep(1);
    }

    /**
     * @Then I load Technician On Call closure time data
     */
    public function IloadClosureTime()
    {
        /** @var ServiceActivity $serviceActivityCommissioning */
        $serviceActivityCommissioning = $this->iriConverter->getResourceFromIri('/service/service_activities/2');
        /** @var ServiceActivity $serviceActivityInfoRequest */
        $serviceActivityInfoRequest = $this->iriConverter->getResourceFromIri('/service/service_activities/7');

        $this->createTechnicianOnCall(createdAt: '-6 days', solvedAt: 'now', title: 'TOC solved under 7 days this month with wrong service activity', description: 'TOC solved under 7 days this month with wrong service activity', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityCommissioning);
        $this->createTechnicianOnCall(createdAt: '-6 days', solvedAt: 'now', title: 'TOC solved under 7 days this month', description: 'TOC solved under 7 days this month', status: TechnicianOnCall::SOLVED);
        $technicianOnCall = $this->createTechnicianOnCall(createdAt: '-6 days', solvedAt: 'now', title: 'TOC solved under 7 days this month', description: 'TOC solved under 7 days this month', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityInfoRequest);
        $this->createCustomerServiceRecord($technicianOnCall);

        $this->createTechnicianOnCall(createdAt: '-7 days', solvedAt: 'now', title: 'TOC solved between 7 and 14 days this month with wrong service activity', description: 'TOC solved between 7 and 14 days this month with wrong service activity', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityCommissioning);
        $this->createTechnicianOnCall(createdAt: '-7 days', solvedAt: 'now', title: 'TOC solved between 7 and 14 days this month', description: 'TOC solved between 7 and 14 days this month', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityInfoRequest);
        $technicianOnCall = $this->createTechnicianOnCall(createdAt: '-10 days', solvedAt: 'now', title: 'TOC solved between 7 and 14 days this month', description: 'TOC solved between 7 and 14 days this month', status: TechnicianOnCall::SOLVED);
        $this->createCustomerServiceRecord($technicianOnCall);
        $this->createTechnicianOnCall(createdAt: '-10 days', solvedAt: 'now', title: 'TOC solved between 7 and 14 days this month', description: 'TOC solved between 7 and 14 days this month', status: TechnicianOnCall::SOLVED);
        $technicianOnCall = $this->createTechnicianOnCall(createdAt: '-13 days', solvedAt: 'now', title: 'TOC solved between 7 and 14 days this month', description: 'TOC solved between 7 and 14 days this month', status: TechnicianOnCall::SOLVED);
        $this->createCustomerServiceRecord($technicianOnCall);

        $this->createTechnicianOnCall(createdAt: '-14 days', solvedAt: 'now', title: 'TOC solved between 14 and 21 days this month with wrong service activity', description: 'TOC solved between 14 and 21 days this month with wrong service activity', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityCommissioning);
        $this->createTechnicianOnCall(createdAt: '-14 days', solvedAt: 'now', title: 'TOC solved between 14 and 21 days this month', description: 'TOC solved between 14 and 21 days this month', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityInfoRequest);
        $technicianOnCall = $this->createTechnicianOnCall(createdAt: '-18 days', solvedAt: 'now', title: 'TOC solved between 14 and 21 days this month', description: 'TOC solved between 14 and 21 days this month', status: TechnicianOnCall::SOLVED);
        $this->createCustomerServiceRecord($technicianOnCall);
        $technicianOnCall = $this->createTechnicianOnCall(createdAt: '-20 days', solvedAt: 'now', title: 'TOC solved between 14 and 21 days this month', description: 'TOC solved between 14 and 21 days this month', status: TechnicianOnCall::SOLVED);
        $this->createCustomerServiceRecord($technicianOnCall);

        $this->createTechnicianOnCall(createdAt: '-21 days', solvedAt: 'now', title: 'TOC solved more than 21 days this month with wrong service activity', description: 'TOC solved more than 21 days this month with wrong service activity', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityCommissioning);
        $this->createTechnicianOnCall(createdAt: '-21 days', solvedAt: 'now', title: 'TOC solved more than 21 days this month', description: 'TOC solved more than 21 days this month', status: TechnicianOnCall::SOLVED, serviceActivity: $serviceActivityInfoRequest);
        $technicianOnCall = $this->createTechnicianOnCall(createdAt: '-23 days', solvedAt: 'now', title: 'TOC solved more than 21 days this month', description: 'TOC solved more than 21 days this month', status: TechnicianOnCall::SOLVED);
        $this->createCustomerServiceRecord($technicianOnCall);

        $this->entityManager->flush();
        sleep(1);
    }

    /**
     * @Then I load Technician On Call KPI data
     */
    public function iLoadKPIData(): void
    {
        /** @var Location $salesOrganisation2 */
        $salesOrganisation2 = $this->iriConverter->getResourceFromIri('/locations/28');

        $this->createTechnicianOnCall(createdAt: '-1 year', title: 'TOC created 1 year ago', description: 'TOC created 1 year ago');
        $this->createTechnicianOnCall(createdAt: '-3 months', solvedAt: '-3 months', title: 'TOC created 3 month ago', description: 'TOC created 3 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-3 months', solvedAt: '-3 months', title: 'TOC created 3 month ago', description: 'TOC created 3 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-3 months', solvedAt: '-2 months', title: 'TOC created 3 month ago', description: 'TOC created 3 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-2 months', solvedAt: '-2 months', title: 'TOC created 2 month ago', description: 'TOC created 2 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-2 months', solvedAt: '-2 months', title: 'TOC created 2 month ago', description: 'TOC created 2 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-2 months', solvedAt: '-2 months', title: 'TOC created 2 month ago', description: 'TOC created 2 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-1 months', solvedAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-1 months', solvedAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago', status: TechnicianOnCall::SOLVED);
        $this->createTechnicianOnCall(createdAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago');
        $this->createTechnicianOnCall(createdAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago');
        $this->createTechnicianOnCall(createdAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago');

        $this->createTechnicianOnCall(createdAt: '-3 months', title: 'TOC created 3 month ago', description: 'TOC created 3 month ago', salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-3 months', title: 'TOC created 3 month ago', description: 'TOC created 3 month ago', salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-3 months', solvedAt: '-3 months', title: 'TOC created 3 month ago', description: 'TOC created 3 month ago', status: TechnicianOnCall::SOLVED, salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-3 months', solvedAt: '-3 months', title: 'TOC created 3 month ago', description: 'TOC created 3 month ago', status: TechnicianOnCall::SOLVED, salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-2 months', title: 'TOC created 2 month ago', description: 'TOC created 2 month ago', salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-2 months', title: 'TOC created 2 month ago', description: 'TOC created 2 month ago', salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-2 months', solvedAt: '-2 months', title: 'TOC created 2 month ago', description: 'TOC created 2 month ago', status: TechnicianOnCall::SOLVED, salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-2 months', solvedAt: '-2 months', title: 'TOC created 2 month ago', description: 'TOC created 2 month ago', status: TechnicianOnCall::SOLVED, salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago', salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago', salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-1 months', solvedAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago', status: TechnicianOnCall::SOLVED, salesOrganisation: $salesOrganisation2);
        $this->createTechnicianOnCall(createdAt: '-1 months', solvedAt: '-1 months', title: 'TOC created 1 month ago', description: 'TOC created 1 month ago', status: TechnicianOnCall::SOLVED, salesOrganisation: $salesOrganisation2);

        $this->entityManager->flush();
        sleep(1);
    }

    /**
     * @Then I load Technician On Call oldest data
     */
    public function iLoadOldestData(): void
    {
        /** @var Location $salesOrganisation2 */
        $salesOrganisation2 = $this->iriConverter->getResourceFromIri('/locations/28');

        $technicianOnCallDuring = $this->createTechnicianOnCall(createdAt: '-1 year -75 days', title: 'TOC Oldest  during 6 months', description: 'TOC Oldest  during 6 months', status: TechnicianOnCall::CLOSED, salesOrganisation: $salesOrganisation2);
        $technicianOnCallSince = $this->createTechnicianOnCall(createdAt: '-6 months -60 days', title: 'TOC Oldest since 6 months', description: 'TOC Oldest since 6 months', salesOrganisation: $salesOrganisation2);
        $technicianOnCallSSO = $this->createTechnicianOnCall(createdAt: '-1 years -50 days', title: 'TOC Oldest sso', description: 'TOC Oldest sso');

        $this->createReport($technicianOnCallDuring, new \DateTimeImmutable('-11 month last day of previous month'), 90);
        $this->createReport($technicianOnCallDuring, new \DateTimeImmutable('-10 month last day of previous month'), 120);
        $this->createReport($technicianOnCallDuring, new \DateTimeImmutable('-9 month last day of previous month'), 150);
        $this->createReport($technicianOnCallDuring, new \DateTimeImmutable('-8 month last day of previous month'), 180);
        $this->createReport($technicianOnCallDuring, new \DateTimeImmutable('-7 month last day of previous month'), 210);
        $this->createReport($technicianOnCallDuring, new \DateTimeImmutable('-6 month last day of previous month'), 240);
        $this->createReport($technicianOnCallSince, new \DateTimeImmutable('-5 month last day of previous month'), 70);
        $this->createReport($technicianOnCallSince, new \DateTimeImmutable('-4 month last day of previous month'), 100);
        $this->createReport($technicianOnCallSince, new \DateTimeImmutable('-3 month last day of previous month'), 130);
        $this->createReport($technicianOnCallSince, new \DateTimeImmutable('-2 month last day of previous month'), 160);
        $this->createReport($technicianOnCallSince, new \DateTimeImmutable('-1 month last day of previous month'), 190);
        $this->createReport($technicianOnCallSince, new \DateTimeImmutable('last day of previous month'), 220);

        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-11 month last day of previous month'), 60);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-10 month last day of previous month'), 90);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-9 month last day of previous month'), 120);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-8 month last day of previous month'), 150);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-7 month last day of previous month'), 180);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-6 month last day of previous month'), 210);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-5 month last day of previous month'), 240);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-4 month last day of previous month'), 270);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-3 month last day of previous month'), 300);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-2 month last day of previous month'), 330);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('-1 month last day of previous month'), 360);
        $this->createReport($technicianOnCallSSO, new \DateTimeImmutable('last day of previous month'), 390);

        $this->entityManager->flush();
        sleep(1);
    }

    public function createIntervention(
        AbstractCustomerServiceRecord $customerServiceRecord,
        string $createdAt = 'now',
        string $endedAt = 'now',
        string $status = Intervention::PENDING,
    ) {
        $intervention = new Intervention();

        $intervention->customerServiceRecord = $customerServiceRecord;
        $intervention->createdAt = new \DateTime($createdAt);
        $intervention->endedAt = new \DateTime($endedAt);
        $intervention->leader = $this->entityManager->getReference(People::class, 64);
        $intervention->setStatus($status);

        $this->entityManager->persist($intervention);

        return $intervention;
    }

    private function createReport(TechnicianOnCall $technicianOnCall, \DateTimeInterface $date, ?int $days = null): void
    {
        $report = new TechnicianOnCall\OldestReport();
        $report->technicianOnCall = $technicianOnCall;
        $report->salesOrganisation = $technicianOnCall->salesOrganisationService;
        $report->date = $date;
        $report->days = $days ?? $date->diff($technicianOnCall->createdAt)->days;

        $this->entityManager->persist($report);
    }

    private function createTechnicianOnCall(
        string $createdAt = 'now',
        ?string $updatedAt = null,
        ?string $solvedAt = null,
        string $title = 'title',
        string $description = 'description',
        string $status = TechnicianOnCall::IN_PROGRESS,
        ?Location $salesOrganisation = null,
        ?ServiceActivity $serviceActivity = null,
    ): TechnicianOnCall {
        /** @var EquipmentRecord $equipmentRecord */
        $equipmentRecord = $this->iriConverter->getResourceFromIri('/equipment_records/9');
        /** @var People $createdBy */
        $createdBy = $this->iriConverter->getResourceFromIri('/people/64');
        /** @var UnitOperationalStatus $unitOperationalStatus */
        $unitOperationalStatus = $this->iriConverter->getResourceFromIri('/unit_operational_statuses/MCP');
        /** @var TechnicianOnCallType $type */
        $type = $this->iriConverter->getResourceFromIri('/service/technician_on_call_types/1');
        /** @var ServiceActivity $serviceActivity */
        $serviceActivity = $serviceActivity ?? $this->iriConverter->getResourceFromIri('/service/service_activities/1');
        /** @var Airport $airport */
        $airport = $this->iriConverter->getResourceFromIri('/airports/110');
        /** @var Location $salesOrganisation */
        $salesOrganisation = $salesOrganisation ?? $this->iriConverter->getResourceFromIri('/locations/23');
        /** @var Customer $customer */
        $customer = $this->iriConverter->getResourceFromIri('/sales/customers/36');
        /** @var ExtranetUser $mainContact */
        $mainContact = $this->iriConverter->getResourceFromIri('/sales/extranet_users/209');

        $technicianOnCall = new TechnicianOnCall();
        $technicianOnCall->title = $title;
        $technicianOnCall->originalTitle = $title;
        $technicianOnCall->description = $description;
        $technicianOnCall->originalDescription = $description;
        $technicianOnCall->equipmentRecord = $equipmentRecord;
        $technicianOnCall->createdBy = $createdBy;
        $technicianOnCall->unitOperationalStatus = $unitOperationalStatus;
        $technicianOnCall->technicianOnCallType = $type;
        $technicianOnCall->serviceActivity = $serviceActivity;
        $technicianOnCall->indiceFactor = IndiceFactor::IF_100->value;
        $technicianOnCall->status = $status;
        $technicianOnCall->airport = $airport;
        $technicianOnCall->salesOrganisationService = $salesOrganisation;
        $technicianOnCall->customer = $customer;
        $technicianOnCall->createdAt = new \DateTime($createdAt);
        $technicianOnCall->updatedAt = $updatedAt ? new \DateTime($updatedAt) : null;
        $technicianOnCall->solvedAt = $solvedAt ? new \DateTime($solvedAt) : null;
        $technicianOnCall->setMainContact($mainContact);

        $this->entityManager->persist($technicianOnCall);

        return $technicianOnCall;
    }

    private function createCustomerServiceRecord(
        TechnicianOnCall $technicianOnCall,
        string $createdAt = 'now',
        string $completedAt = 'now',
        ?string $description = null,
        string $status = AbstractCustomerServiceRecord::PENDING,
        ?EquipmentRecord $equipmentRecord = null,
    ): TechnicianOnCallCustomerServiceRecord {
        $customerServiceRecord = new TechnicianOnCallCustomerServiceRecord();
        $customerServiceRecord->createdAt = new \DateTime($createdAt);
        $customerServiceRecord->completedAt = new \DateTime($completedAt);
        $customerServiceRecord->description = $description ?? $technicianOnCall->description;
        $customerServiceRecord->equipmentRecord = $equipmentRecord ?? $technicianOnCall->equipmentRecord;
        $customerServiceRecord->setTechnicianOnCall($technicianOnCall);
        $customerServiceRecord->setStatus($status);

        $this->entityManager->persist($customerServiceRecord);

        return $customerServiceRecord;
    }
}
