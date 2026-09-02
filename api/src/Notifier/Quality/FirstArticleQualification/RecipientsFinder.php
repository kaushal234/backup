<?php

declare(strict_types=1);

namespace App\Notifier\Quality\FirstArticleQualification;

use App\Entity\Quality\FirstArticleQualification\FirstArticleQualification;
use App\Manager\Quality\FirstArticleQualification\FirstArticleQualificationDateReminderManager;
use App\Repository\Directory\PeopleRepository;
use Psr\Container\ContainerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;

class RecipientsFinder implements ServiceSubscriberInterface
{
    private readonly ContainerInterface $serviceLocator;

    public function __construct(ContainerInterface $serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }

    /**
     * @param FirstArticleQualification[] $firstArticleQualifications
     */
    public function findReminderStatusRecipients(array $firstArticleQualifications, string $reminderStatus): array
    {
        $recipients = [];
        switch ($reminderStatus) {
            case FirstArticleQualificationDateReminderManager::PLAN_DUE_DATE_PASSED:
            case FirstArticleQualificationDateReminderManager::DUE_DATE_PASSED:
            case FirstArticleQualificationDateReminderManager::DELIVERABLES_DUE_DATE_PASSED:
                foreach ($firstArticleQualifications as $firstArticleQualification) {
                    $recipients[] = $firstArticleQualification->getOwner()->getEmail();
                }
                break;
            case FirstArticleQualificationDateReminderManager::PLAN_DUE_DATE_SOON:
            case FirstArticleQualificationDateReminderManager::DUE_DATE_SOON:
                foreach ($firstArticleQualifications as $firstArticleQualification) {
                    $recipients[] = $firstArticleQualification->getPoster()->getEmail();
                }
                break;
            default:
                break;
        }

        return $recipients;
    }

    public function findStatusRecipients(FirstArticleQualification $firstArticleQualification): array
    {
        $peopleRepository = $this->serviceLocator->get(PeopleRepository::class);
        $recipients = [$firstArticleQualification->getOwner()];
        switch ($firstArticleQualification->getStatus()) {
            case FirstArticleQualification::CONDITIONAL:
                $recipients = [
                    ...$peopleRepository->findGroupMembers('ROLE_MLM', $location = $firstArticleQualification->getLocation()),
                    ...$peopleRepository->findGroupMembers('ROLE_EM', $location),
                    ...$peopleRepository->findGroupMembers('ROLE_QAM', $location),
                    ...$peopleRepository->findGroupMembers('ROLE_FAQ', $location), ];
                break;
            case FirstArticleQualification::PENDING:
            case FirstArticleQualification::IN_PROGRESS:
            case FirstArticleQualification::IN_PROGRESS_PLAN_COMPLETED:
            case FirstArticleQualification::REJECTED:
            case FirstArticleQualification::QUALIFIED:
                $recipients[] = $firstArticleQualification->getPoster();

                if (null !== ($buyer = $firstArticleQualification->getBuyer())) {
                    $recipients[] = $buyer;
                }

                foreach ($firstArticleQualification->getMembers() as $member) {
                    $recipients[] = $member;
                }
        }

        return $recipients;
    }

    public function findPlanCompletedRecipients(FirstArticleQualification $firstArticleQualification): array
    {
        $peopleRepository = $this->serviceLocator->get(PeopleRepository::class);

        return $peopleRepository->findGroupMembers('ROLE_QE', $firstArticleQualification->getLocation());
    }

    public function findCCs(FirstArticleQualification $firstArticleQualification): array
    {
        $ccs = [$firstArticleQualification->getPoster()->getEmail()];
        if (null !== $buyer = $firstArticleQualification->getBuyer()) {
            $ccs[] = $buyer->getEmail();
        }

        foreach ($firstArticleQualification->getMembers() as $member) {
            $ccs[] = $member->getEmail();
        }

        return $ccs;
    }

    public static function getSubscribedServices(): array
    {
        return [
            PeopleRepository::class,
        ];
    }
}
