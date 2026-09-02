<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler\Service;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operations;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use App\Entity\Directory\Location;
use App\Entity\EquipmentRecord;
use App\Entity\Sales\Customer;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\User;
use App\Factory\Service\TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory;
use App\Factory\Service\TracteasyHelpdeskIssueFactory;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\DataProvider\JiraItemDataProvider;
use App\Jira\Resources\TracteasyHelpdeskIssue;
use App\Jira\Resources\TracteasyIssueType;
use App\Message\Service\TechnicianOnCallJiraTracteasyIssueCreate;
use App\MessageHandler\Service\TechnicianOnCallJiraTracteasyIssueCreateHandler;
use App\Repository\Directory\LocationRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class TechnicianOnCallJiraTracteasyIssueCreateHandlerTest extends TestCase
{
    public function testDoesNothingWhenTocHasNoEquipmentRecord(): void
    {
        $toc = new TechnicianOnCall();
        $user = new User();

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/service/technician_on_calls/7', [], null, $toc],
            ['/directory/people/1', [], null, $user],
        ]);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::never())->method('flush');

        $processor = $this->createMock(JiraDataProcessor::class);
        $processor->expects(self::never())->method('process');

        $handler = new TechnicianOnCallJiraTracteasyIssueCreateHandler(
            $iriConverter,
            $entityManager,
            $this->createMock(LocationRepository::class),
            $this->createMock(TracteasyHelpdeskIssueFactory::class),
            $this->createMock(TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory::class),
            $this->resourceMetadataFactory(),
            $this->createMock(JiraItemDataProvider::class),
            $processor,
        );

        $handler(new TechnicianOnCallJiraTracteasyIssueCreate('/service/technician_on_calls/7', '/directory/people/1'));
    }

    public function testPersistsANoProjectKeyCommentWhenCustomerHasNoJiraProjectKey(): void
    {
        $toc = $this->technicianOnCall();
        $toc->customer->setEasymileJiraProjectKey(null);
        $user = new User();

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/service/technician_on_calls/7', [], null, $toc],
            ['/directory/people/1', [], null, $user],
        ]);

        $locationRepository = $this->createMock(LocationRepository::class);
        $locationRepository->method('findOneBy')->with(['name' => 'TRACTEASY'])->willReturn($toc->salesOrganisationService);

        $commentFactory = $this->createMock(TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory::class);
        $commentFactory->expects(self::once())->method('createNoProjectKeyMessage')->with($toc, $user)->willReturn(new \App\Entity\Activity\Comment());
        $commentFactory->expects(self::never())->method('create');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $processor = $this->createMock(JiraDataProcessor::class);
        $processor->expects(self::never())->method('process');

        $handler = new TechnicianOnCallJiraTracteasyIssueCreateHandler(
            $iriConverter,
            $entityManager,
            $locationRepository,
            $this->createMock(TracteasyHelpdeskIssueFactory::class),
            $commentFactory,
            $this->resourceMetadataFactory(),
            $this->createMock(JiraItemDataProvider::class),
            $processor,
        );

        $handler(new TechnicianOnCallJiraTracteasyIssueCreate('/service/technician_on_calls/7', '/directory/people/1'));
    }

    public function testCreatesTheIssueAndSetsTheKeyOnHappyPath(): void
    {
        $toc = $this->technicianOnCall();
        $user = new User();
        $issueType = new TracteasyIssueType();

        $iriConverter = $this->createMock(IriConverterInterface::class);
        $iriConverter->method('getResourceFromIri')->willReturnMap([
            ['/service/technician_on_calls/7', [], null, $toc],
            ['/directory/people/1', [], null, $user],
        ]);

        $locationRepository = $this->createMock(LocationRepository::class);
        $locationRepository->method('findOneBy')->with(['name' => 'TRACTEASY'])->willReturn($toc->salesOrganisationService);

        $issueTypeProvider = $this->createMock(JiraItemDataProvider::class);
        $issueTypeProvider->method('provide')->willReturn($issueType);

        $issue = new TracteasyHelpdeskIssue();
        $issueFactory = $this->createMock(TracteasyHelpdeskIssueFactory::class);
        $issueFactory->expects(self::once())->method('create')->with($toc, $issueType)->willReturn($issue);

        $createdIssue = new TracteasyHelpdeskIssue();
        $createdIssue->key = 'AIRB-99';
        $processor = $this->createMock(JiraDataProcessor::class);
        $processor->expects(self::once())->method('process')->with($issue, self::isInstanceOf(Post::class))->willReturn($createdIssue);

        $commentFactory = $this->createMock(TechnicianOnCallTracteasyHelpdeskIssueCreationCommentFactory::class);
        $commentFactory->expects(self::once())->method('create')->with($toc, $user, $issueType)->willReturn(new \App\Entity\Activity\Comment());

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('flush');

        $handler = new TechnicianOnCallJiraTracteasyIssueCreateHandler(
            $iriConverter,
            $entityManager,
            $locationRepository,
            $issueFactory,
            $commentFactory,
            $this->resourceMetadataFactory(),
            $issueTypeProvider,
            $processor,
        );

        $handler(new TechnicianOnCallJiraTracteasyIssueCreate('/service/technician_on_calls/7', '/directory/people/1'));

        self::assertSame('AIRB-99', $toc->jiraTracteasyIssueKey);
    }

    private function resourceMetadataFactory(): ResourceMetadataCollectionFactoryInterface
    {
        $issueTypeOperation = new Get(class: TracteasyIssueType::class);
        $issueTypeResource = (new ApiResource())->withOperations(new Operations([$issueTypeOperation]));
        $issueTypeCollection = new ResourceMetadataCollection(TracteasyIssueType::class, [$issueTypeResource]);

        $issueOperation = new Post(name: 'jira_tracteasy_post_issue', class: TracteasyHelpdeskIssue::class);
        $issueResource = (new ApiResource())->withOperations(new Operations(['jira_tracteasy_post_issue' => $issueOperation]));
        $issueCollection = new ResourceMetadataCollection(TracteasyHelpdeskIssue::class, [$issueResource]);

        $factory = $this->createMock(ResourceMetadataCollectionFactoryInterface::class);
        $factory->method('create')->willReturnMap([
            [TracteasyIssueType::class, $issueTypeCollection],
            [TracteasyHelpdeskIssue::class, $issueCollection],
        ]);

        return $factory;
    }

    private function technicianOnCall(): TechnicianOnCall
    {
        $toc = new TechnicianOnCall();
        $toc->equipmentRecord = new EquipmentRecord();
        $toc->salesOrganisationService = new Location();

        $customer = new Customer();
        $customer->setEasymileJiraProjectKey('AIRB');
        $toc->customer = $customer;

        return $toc;
    }
}
