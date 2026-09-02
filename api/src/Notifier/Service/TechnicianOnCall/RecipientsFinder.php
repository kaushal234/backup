<?php

declare(strict_types=1);

namespace App\Notifier\Service\TechnicianOnCall;

use App\Entity\Directory\People;
use App\Entity\IndiceFactor;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCall\TechnicianOnCallSurvey;
use App\Entity\Service\TechnicianOnCallTag;
use App\Entity\User;
use App\Factory\SanitizedEmailListFactory;
use App\Manager\Directory\TeamMemberManager;
use App\Notifier\UserSettingSubscriptionResolver;
use App\Repository\Common\SubscriptionRepository;
use App\Repository\Directory\LocationRepository;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Directory\PositionRepository;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\SecurityBundle\Security;

class RecipientsFinder
{
    public function __construct(
        private readonly PeopleRepository $peopleRepository,
        private readonly PositionRepository $positionRepository,
        private readonly LocationRepository $locationRepository,
        private readonly UserSettingSubscriptionResolver $subscriptionResolver,
        private readonly TeamMemberManager $teamMemberManager,
        private readonly SanitizedEmailListFactory $sanitizedEmailListFactory,
        private readonly SubscriptionRepository $subscriptionRepository,
        private readonly Security $security,
    ) {
    }

    public function findTos(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall, array $context = []): array
    {
        $currentUser = $this->security->getUser();

        $tos = match ($subject) {
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL => [
                $currentUser,
                $technicianOnCall->assignee,
                $technicianOnCall->technician,
                $technicianOnCall->technician?->getSupervisor(),
                $technicianOnCall->createdBy instanceof People ? $technicianOnCall->createdBy : null,
                ...$this->getAdditionalRecipientsDependingOnIndiceFactor($technicianOnCall),
                ...$this->getFollowersRecipients($technicianOnCall),
                $this->tagIhsRecipients($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_EXTERNAL,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_EXTERNAL,
            TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL,
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_EXTERNAL, => [
                $technicianOnCall->getMainContact(),
            ],
            TechnicianOnCallMailSubject::TOC_CREATED_BY_CUSTOMER => [
                $technicianOnCall->createdBy,
                $technicianOnCall->equipmentRecord?->getSalesOrganisation()->getContact()->getServiceHubEmail(),
                $technicianOnCall->salesOrganisationService->getContact()->getServiceHubEmail(),
                ...$this->getAdditionalRecipientsDependingOnIndiceFactor($technicianOnCall),
                ...$this->getFollowersRecipients($technicianOnCall),
                $this->tagIhsRecipients($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_UPDATED => [
                $currentUser,
                $technicianOnCall->assignee,
                $technicianOnCall->technician,
                $technicianOnCall->technician?->getSupervisor(),
                ...$this->getAdditionalRecipientsDependingOnIndiceFactor($technicianOnCall),
                ...($technicianOnCall->factoryFlag ? $this->getRecipientsForFactoryFlag($technicianOnCall) : []),
                ...$this->getFollowersRecipients($technicianOnCall),
                $this->tagIhsRecipients($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_INTERNAL,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_BY_CUST,
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_INTERNAL => [
                $currentUser instanceof People ? $currentUser : null,
                $technicianOnCall->createdBy instanceof People ? $technicianOnCall->createdBy : null,
                $technicianOnCall->assignee,
                $technicianOnCall->assignee?->getSupervisor(),
                $technicianOnCall->technician,
                $technicianOnCall->technician?->getSupervisor(),
                ...($technicianOnCall->factoryFlag ? $this->getRecipientsForFactoryFlag($technicianOnCall) : []),
                ...$this->getAdditionalRecipientsDependingOnIndiceFactor($technicianOnCall),
                ...$this->getFollowersRecipients($technicianOnCall),
                $this->tagIhsRecipients($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_FACTORY_FLAG => [
                $currentUser,
                $technicianOnCall->createdBy instanceof People ? $technicianOnCall->createdBy : null,
                $technicianOnCall->assignee,
                $technicianOnCall->assignee?->getSupervisor(),
                $technicianOnCall->technician,
                $technicianOnCall->technician?->getSupervisor(),
                ...$this->getRecipientsForFactoryFlag($technicianOnCall),
                ...$this->getFollowersRecipients($technicianOnCall),
                $this->tagIhsRecipients($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_BLACK_CAT => [
                $currentUser,
                ...$this->getRecipientsForBlackCat($technicianOnCall),
                $technicianOnCall->salesOrganisationService->getContact()->getServiceHubEmail(),
                ...$this->getFollowersRecipients($technicianOnCall),
                $this->tagIhsRecipients($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_CSR_CREATED => [
                $context['customerServiceRecord']->getOpenIntervention()->leader,
                $context['customerServiceRecord']->getOpenIntervention()->leader?->getSupervisor(),
                $technicianOnCall->assignee,
                $technicianOnCall->technician,
                $technicianOnCall->technician?->getSupervisor(),
                ...$this->getFollowersRecipients($technicianOnCall),
                $this->tagIhsRecipients($technicianOnCall),
            ],
        };

        return $this->sanitizedEmailListFactory->buildCleanEmailAddressList($tos);
    }

    public function findCcs(TechnicianOnCallMailSubject $subject, TechnicianOnCall $technicianOnCall, array $context = []): array
    {
        $ccs = match ($subject) {
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_INTERNAL => [
                $technicianOnCall->assignee?->getSupervisor(),
                ...$this->getSubscribers($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_CREATED_BY_PEOPLE_EXTERNAL,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_EXTERNAL,
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_EXTERNAL => [
                ...$technicianOnCall->getContacts(),
            ],
            TechnicianOnCallMailSubject::TOC_SOLVED_EXTERNAL => [
                ...$technicianOnCall->getContacts(),
                $this->security->getUser(),
            ],
            TechnicianOnCallMailSubject::TOC_CREATED_BY_CUSTOMER => [
                $technicianOnCall->customer->getMainSalesRepresentative()?->asm,
                ...$this->getSubscribers($technicianOnCall),
            ],
            TechnicianOnCallMailSubject::TOC_UPDATED,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_INTERNAL,
            TechnicianOnCallMailSubject::TOC_PENDING_TO_IN_PROGRESS_INTERNAL,
            TechnicianOnCallMailSubject::TOC_NEW_COMMENT_BY_CUST,
            TechnicianOnCallMailSubject::TOC_FACTORY_FLAG,
            TechnicianOnCallMailSubject::TOC_BLACK_CAT => [...$this->getSubscribers($technicianOnCall)],
            TechnicianOnCallMailSubject::TOC_CSR_CREATED => [],
        };

        return $this->sanitizedEmailListFactory->buildCleanEmailAddressList($ccs);
    }

    public function findSurveyTos(TechnicianOnCallSurvey $survey): array
    {
        $to = [];

        $to[] = $survey->technicianOnCall->createdBy->getEmail();
        $to[] = $survey->technicianOnCall->getCurrentCustomerServiceRecord()?->getOpenIntervention()?->leader->getEmail();
        $to[] = $survey->technicianOnCall->assignee->getEmail();

        foreach ($survey->technicianOnCall->customer->getCrt() as $crt) {
            if ($crt->getErpLocation() !== $survey->technicianOnCall->salesOrganisationService) {
                continue;
            }

            $to[] = $crt->getSalesRepresentative()?->getEmail();
        }

        foreach ($this->peopleRepository->findGroupsMembers(['ROLE_CSM', 'ROLE_EVP', 'ROLE_CEO'], $survey->technicianOnCall->salesOrganisationService) as $people) {
            $to[] = $people->getEmail();
        }

        foreach ($this->peopleRepository->findGroupsMembers(['ROLE_CSM', 'ROLE_COO', 'ROLE_GCOO'], $this->locationRepository->findOneBy(['erp' => 900])) as $people) {
            $to[] = $people->getEmail();
        }

        return $this->sanitizedEmailListFactory->buildCleanEmailAddressList($to);
    }

    private function getSupervisorAndCsmHierarchyRecipients(?People $people): array
    {
        $recipients = [];

        $supervisor = $people?->getSupervisor();
        if (!$supervisor instanceof User) {
            return $recipients;
        }

        $recipients[] = $supervisor;

        $csmPosition = $this->positionRepository->findOneBy(['code' => 'CSM']);
        if (null !== $csmPosition && $supervisor->getPosition() !== $csmPosition) {
            $csmHierarchyList = $this->teamMemberManager->get([
                'user' => $people->getId(),
                'supervisor_position' => ['CSM'],
            ]);
            $firstCsmInHierarchy = \count($csmHierarchyList) > 0 ? $this->peopleRepository->find($csmHierarchyList[0]['id']) : null;
            if ($firstCsmInHierarchy instanceof User) {
                $recipients[] = $firstCsmInHierarchy;
            }
        }

        return $recipients;
    }

    private function getAdditionalRecipientsDependingOnIndiceFactor(TechnicianOnCall $technicianOnCall): array
    {
        $additionalRecipients = [];

        // Cases for IF_10, IF_100, IF_1000
        // -> add supervisor of assignee and first CSM in Hierarchy to additional recipients
        // -> add supervisor of technician and first CSM in Hierarchy to additional recipients
        if (\in_array($technicianOnCall->indiceFactor, [
            IndiceFactor::IF_10->value,
            IndiceFactor::IF_100->value,
            IndiceFactor::IF_1000->value,
        ], true)) {
            $additionalRecipients = [
                ...$additionalRecipients,
                ...$this->getSupervisorAndCsmHierarchyRecipients($technicianOnCall->assignee),
                ...$this->getSupervisorAndCsmHierarchyRecipients($technicianOnCall->technician),
            ];
        }

        // Cases for IF_100 & IF_1000 -> add GCSD, Factory and SSO members to additional recipients
        if (\in_array($technicianOnCall->indiceFactor, [IndiceFactor::IF_100->value, IndiceFactor::IF_1000->value], true)) {
            $gcsdPosition = $this->positionRepository->findOneBy(['code' => 'GCSD']);
            $gcsdMembers = null !== $gcsdPosition ? $this->peopleRepository->findBy(['position' => $gcsdPosition, 'disabled' => false]) : [];

            $manufacturerLocation = $technicianOnCall->equipmentRecord?->getManufacturerLocation();
            $manufacturerMembers = null !== $manufacturerLocation
                ? $this->peopleRepository->findGroupsMembers(['ROLE_RME', 'ROLE_EM', 'ROLE_PSM', 'ROLE_PSE', 'ROLE_COO', 'ROLE_RCEO', 'ROLE_CEO'], $manufacturerLocation)
                : [];

            $salesOrgMembers = $this->peopleRepository->findGroupsMembers(
                ['ROLE_EVP'],
                $technicianOnCall->salesOrganisationService
            );

            if (null !== $technicianOnCall->customer) {
                $additionalRecipients = [
                    ...$additionalRecipients,
                    ...$this->peopleRepository->findAllMainAsmForCustomerHierarchy($technicianOnCall->customer),
                ];
            }

            $additionalRecipients = [
                ...$additionalRecipients,
                ...$manufacturerMembers,
                ...$salesOrgMembers,
                ...$gcsdMembers,
            ];
        }

        // Case for IF_1000 -> add QAM, CEO and ALVEST members to additional recipients
        if ($technicianOnCall->indiceFactor === IndiceFactor::IF_1000->value) {
            $alvest = $this->locationRepository->findOneBy(['name' => 'ALVEST']);

            $qamMembers = null !== $technicianOnCall->equipmentRecord
                ? $this->peopleRepository->findGroupsMembers(['ROLE_QAM'], $technicianOnCall->equipmentRecord->getManufacturerLocation())
                : [];

            $ceoMembers = null !== $technicianOnCall->equipmentRecord
                ? $this->peopleRepository->findGroupsMembers(['ROLE_CEO', 'ROLE_RCEO'], $technicianOnCall->equipmentRecord->getSalesOrganisation())
                : [];

            $alvestMembers = $this->peopleRepository->findGroupsMembers(
                ['ROLE_GCFO', 'ROLE_GPID', 'ROLE_CMO'],
                $alvest
            );

            $positions = $this->positionRepository->findBy(['code' => ['GCH', 'GCTO', 'TCEO']]);
            $chairmanGctoAndTceoMembers = [];

            foreach ($positions as $position) {
                $chairmanGctoAndTceoMembers = [...$chairmanGctoAndTceoMembers, ...$this->peopleRepository->findBy(['position' => $position, 'disabled' => false])];
            }

            $secondaryAsmList = null !== $technicianOnCall->customer
                ? $this->peopleRepository->findAllSecondaryAsmsForCustomerHierarchy($technicianOnCall->customer)
                : [];

            $additionalRecipients = [
                ...$additionalRecipients,
                ...$qamMembers,
                ...$ceoMembers,
                ...$alvestMembers,
                ...$chairmanGctoAndTceoMembers,
                ...$secondaryAsmList,
            ];
        }

        return $additionalRecipients;
    }

    private function getRecipientsForFactoryFlag(TechnicianOnCall $technicianOnCall): array
    {
        $recipients = [
            $technicianOnCall->assignee,
            $technicianOnCall->assignee->getSupervisor(),
            $technicianOnCall->customer->getMainSalesRepresentative()?->asm,
            ...array_map(
                static fn ($representative) => $representative->asm,
                $technicianOnCall->customer->getSecondarySalesRepresentatives()->toArray() ?? []
            ),
            ...$this->peopleRepository->findGroupsMembers(
                ['ROLE_EVP', 'CSM'], $technicianOnCall->salesOrganisationService
            ),
        ];

        if (null !== $technicianOnCall->equipmentRecord) {
            $recipients = [
                ...$recipients,
                ...$this->peopleRepository->findAllByPositionByLocation(
                    $this->positionRepository->findOneBy(['code' => 'DPM']),
                    $technicianOnCall->equipmentRecord->getSalesOrganisation()
                ),
                ...$this->peopleRepository->findGroupsMembers(
                    ['ROLE_PSM', 'ROLE_PSE', 'ROLE_RME', 'ROLE_EM', 'ROLE_COO'],
                    $technicianOnCall->equipmentRecord->getManufacturerLocation()
                ),
            ];
        }

        return array_filter($recipients, static fn ($recipient) => $recipient instanceof People);
    }

    private function getRecipientsForBlackCat(TechnicianOnCall $technicianOnCall): array
    {
        $recipients = [];
        if (null === $technicianOnCall->equipmentRecord) {
            return $recipients;
        }

        $recipients = [
            $technicianOnCall->equipmentRecord->getBuyer()?->getMainSalesRepresentative()?->asm,
            $technicianOnCall->equipmentRecord->getEndUser()?->getMainSalesRepresentative()?->asm,
            ...$this->peopleRepository->findAllByPositionByLocation(
                $this->positionRepository->findOneBy(['code' => 'DPM']),
                $technicianOnCall->equipmentRecord->getSalesOrganisation()
            ),
            ...$this->peopleRepository->findGroupsMembers(
                ['ROLE_CSM', 'ROLE_EVP', 'ROLE_RCEO'],
                $technicianOnCall->equipmentRecord->getSalesOrganisation()
            ),
            ...$this->peopleRepository->findGroupsMembers(
                ['ROLE_PSM', 'ROLE_PSE', 'ROLE_EM', 'ROLE_RME', 'ROLE_COO', 'ROLE_RCOO', 'ROLE_RCEO'],
                $technicianOnCall->equipmentRecord->getManufacturerLocation()
            ),
        ];

        if ($assignee = $technicianOnCall->assignee) {
            $cstlPosition = $this->positionRepository->findOneBy(['code' => 'CSTL']);
            if (null !== $cstlPosition && $assignee->getPosition() !== $cstlPosition) {
                $cstlHierarchyList = $this->teamMemberManager->get([
                    'user' => $technicianOnCall->assignee->getId(),
                    'supervisor_position' => ['CSTL'],
                ]);
                $firstCstlInHierarchy = \count($cstlHierarchyList) > 0 ? $this->peopleRepository->find($cstlHierarchyList[0]['id']) : null;
                if ($firstCstlInHierarchy instanceof People) {
                    $recipients[] = $firstCstlInHierarchy;
                }
            }
        }

        return array_unique(array_filter($recipients, static fn ($recipient) => $recipient instanceof People));
    }

    private function getFollowersRecipients(TechnicianOnCall $technicianOnCall)
    {
        $tos = [];
        foreach ($this->subscriptionRepository->findByResource($technicianOnCall) as $subscription) {
            if (!$subscription->getUser() instanceof People) {
                continue;
            }

            $tos[] = $subscription->getUser();
        }

        return $tos;
    }

    private function tagIhsRecipients(TechnicianOnCall $technicianOnCall): ?string
    {
        $criteria = Criteria::create(true)
            ->where(Criteria::expr()->eq('name', TechnicianOnCallTag::IHS))
        ;
        $result = $technicianOnCall->getTags()->matching($criteria);

        if ($result->isEmpty()) {
            return null;
        }

        return 'support@smart-airport-systems.com';
    }

    private function getSubscribers(TechnicianOnCall $technicianOnCall): array
    {
        return $this->subscriptionResolver->findSubscribers(
            'toc.subscriptions',
            $technicianOnCall,
            ['indiceFactor']
        );
    }
}
