<?php

declare(strict_types=1);

namespace App\Tests\Jira\Resolver;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Jira\Resolver\JiraContextResolver;
use App\Jira\Resources\IssueType;
use App\Jira\Resources\Priority;
use App\Jira\Resources\TracteasyHelpdeskIssue;
use App\Jira\Resources\TracteasyIssueType;
use App\Jira\Resources\TroubleTicketIssue;
use App\Jira\Resources\UserStoryIssue;
use PHPUnit\Framework\TestCase;

class JiraContextResolverTest extends TestCase
{
    private JiraContextResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new JiraContextResolver();
    }

    public function testResolveWithJiraContextExtraProperty(): void
    {
        $operation = new Post(
            class: TracteasyHelpdeskIssue::class,
            extraProperties: ['jira_context' => 'tracteasy']
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('tracteasy', $context);
    }

    public function testResolveFromResourceClassNameContainingTracteasy(): void
    {
        $operation = new Post(
            class: TracteasyHelpdeskIssue::class,
            extraProperties: ['jira_context' => 'tracteasy']
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('tracteasy', $context);
    }

    public function testResolveFromOperationNameContainingTracteasy(): void
    {
        $operation = new Post(
            class: TracteasyHelpdeskIssue::class,
            name: 'jira_tracteasy_post_issue',
            extraProperties: ['jira_context' => 'tracteasy']
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('tracteasy', $context);
    }

    public function testResolveDefaultsToJira(): void
    {
        $operation = new Post(
            class: TroubleTicketIssue::class,
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('jira', $context);
    }

    public function testResolveJiraIssueType(): void
    {
        $operation = new Get(
            class: IssueType::class,
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('jira', $context);
    }

    public function testResolveTracteasyIssueType(): void
    {
        $operation = new Get(
            class: TracteasyIssueType::class,
            extraProperties: ['jira_context' => 'tracteasy']
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('tracteasy', $context);
    }

    public function testResolveJiraPriority(): void
    {
        $operation = new Get(
            class: Priority::class,
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('jira', $context);
    }

    public function testResolveUserStoryIssue(): void
    {
        $operation = new Post(
            class: UserStoryIssue::class,
        );

        $context = $this->resolver->resolve($operation);

        $this->assertSame('jira', $context);
    }

    /**
     * @dataProvider contextPriorityProvider
     */
    public function testContextPriority(Post $operation, string $expectedContext): void
    {
        $context = $this->resolver->resolve($operation);

        $this->assertSame($expectedContext, $context);
    }

    public function contextPriorityProvider(): \Generator
    {
        yield 'extra_property_wins_over_class_name' => [
            new Post(
                class: TracteasyHelpdeskIssue::class,
                extraProperties: ['jira_context' => 'jira']
            ),
            'jira',
        ];

        yield 'tracteasy_from_extra_properties' => [
            new Post(
                class: TracteasyIssueType::class,
                extraProperties: ['jira_context' => 'tracteasy']
            ),
            'tracteasy',
        ];
    }
}
