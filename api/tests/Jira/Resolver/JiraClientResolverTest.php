<?php

declare(strict_types=1);

namespace App\Tests\Jira\Resolver;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Http\JiraClient;
use App\Http\JiraTracteasyClient;
use App\Jira\Registry\JiraClientRegistry;
use App\Jira\Resolver\JiraClientResolver;
use App\Jira\Resolver\JiraContextResolver;
use App\Jira\Resources\IssueType;
use App\Jira\Resources\TracteasyHelpdeskIssue;
use App\Jira\Resources\TracteasyIssueType;
use App\Jira\Resources\TroubleTicketIssue;
use App\Jira\Resources\UserStoryIssue;
use PHPUnit\Framework\TestCase;

class JiraClientResolverTest extends TestCase
{
    private JiraClientResolver $resolver;
    private JiraContextResolver $contextResolver;
    private JiraClientRegistry $registry;
    private JiraClient $jiraClient;
    private JiraTracteasyClient $tracteasyClient;

    protected function setUp(): void
    {
        $this->jiraClient = $this->createMock(JiraClient::class);
        $this->tracteasyClient = $this->createMock(JiraTracteasyClient::class);

        $clients = [
            'jira' => $this->jiraClient,
            'tracteasy' => $this->tracteasyClient,
        ];

        $this->registry = new JiraClientRegistry(new \ArrayIterator($clients));
        $this->contextResolver = new JiraContextResolver();
        $this->resolver = new JiraClientResolver($this->registry, $this->contextResolver);
    }

    public function testResolveJiraClientForTroubleTicketIssue(): void
    {
        $operation = new Post(
            class: TroubleTicketIssue::class,
        );

        $client = $this->resolver->resolve($operation);

        $this->assertSame($this->jiraClient, $client);
    }

    public function testResolveJiraClientForUserStoryIssue(): void
    {
        $operation = new Post(
            class: UserStoryIssue::class,
        );

        $client = $this->resolver->resolve($operation);

        $this->assertSame($this->jiraClient, $client);
    }

    public function testResolveJiraClientForIssueType(): void
    {
        $operation = new Get(
            class: IssueType::class,
        );

        $client = $this->resolver->resolve($operation);

        $this->assertSame($this->jiraClient, $client);
    }

    public function testResolveTracteasyClientForTracteasyHelpdeskIssue(): void
    {
        $operation = new Post(
            class: TracteasyHelpdeskIssue::class,
            extraProperties: ['jira_context' => 'tracteasy']
        );

        $client = $this->resolver->resolve($operation);

        $this->assertSame($this->tracteasyClient, $client);
    }

    public function testResolveTracteasyClientForTracteasyIssueType(): void
    {
        $operation = new Get(
            class: TracteasyIssueType::class,
            extraProperties: ['jira_context' => 'tracteasy']
        );

        $client = $this->resolver->resolve($operation);

        $this->assertSame($this->tracteasyClient, $client);
    }

    public function testResolveWithExplicitContext(): void
    {
        $operation = new Post(
            class: TroubleTicketIssue::class,
            extraProperties: ['jira_context' => 'tracteasy']
        );

        $client = $this->resolver->resolve($operation);

        $this->assertSame($this->tracteasyClient, $client);
    }

    public function testResolveOverrideTracteasyToJira(): void
    {
        $operation = new Post(
            class: TracteasyHelpdeskIssue::class,
            extraProperties: ['jira_context' => 'jira']
        );

        $client = $this->resolver->resolve($operation);

        $this->assertSame($this->jiraClient, $client);
    }
}
