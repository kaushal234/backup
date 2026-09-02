<?php

declare(strict_types=1);

namespace App\MessageHandler\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Factory\Service\TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory;
use App\Factory\Service\TracteasyHelpdeskIssueFactory;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\Resources\TracteasyIssueType;
use App\Message\Service\TechnicianOnCallJiraTracteasyIssueCreate;
use App\Repository\Directory\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class TechnicianOnCallJiraTracteasyIssueCreateHandler
{
    public function __construct(
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
        private readonly LocationRepository $locationRepository,
        private readonly TracteasyHelpdeskIssueFactory $tracteasyHelpdeskIssueFactory,
        private readonly TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory $commentFactory,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataFactory,
        private readonly JiraItemDataProvider $issueTypeProvider,
        private readonly JiraDataProcessor $processor,
    ) {
    }

    public function __invoke(TechnicianOnCallJiraTracteasyIssueCreate $message): void
    {
        /** @var TechnicianOnCall $toc */
        $toc = $this->iriConverter->getResourceFromIri($message->getTechnicianOnCallIri());
        /** @var User $user */
        $user = $this->iriConverter->getResourceFromIri($message->getUserIri());

        if (null === $toc->equipmentRecord) {
            return;
        }

        $tracteasyLocation = $this->locationRepository->findOneBy(['name' => 'TRACTEASY']);

        if ($tracteasyLocation !== $toc->salesOrganisationService) {
            return;
        }

        if (null === $toc->customer->getEasymileJiraProjectKey()) {
            $comment = $this->commentFactory->createNoProjectKeyMessage($toc, $user);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return;
        }

        $issueOperation = $this->resourceMetadataFactory->create(TracteasyIssueType::class)->getOperation();

        $issueType = $this->issueTypeProvider->provide(
            $issueOperation,
            ['id' => TracteasyIssueType::ISSUE_ISSUETYPE_ID]
        );

        if (null === $issueType) {
            $comment = $this->commentFactory->create($toc, $user);
            $this->entityManager->persist($comment);
            $this->entityManager->flush();

            return;
        }

        $issue = $this->tracteasyHelpdeskIssueFactory->create($toc, $issueType);
        $operation = $this->resourceMetadataFactory->create($issue::class)->getOperation('jira_tracteasy_post_issue');
        $createdIssue = $this->processor->process($issue, $operation);

        $toc->jiraTracteasyIssueKey = $createdIssue->key;
        $this->entityManager->persist($toc);

        $comment = $this->commentFactory->create($toc, $user, $issueType);
        $this->entityManager->persist($comment);
        $this->entityManager->flush();
    }
}
