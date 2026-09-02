<?php

declare(strict_types=1);

namespace App\Tests\Jira\Registry;

use App\Http\JiraClient;
use App\Http\JiraTracteasyClient;
use App\Jira\Http\JiraClientInterface;
use App\Jira\Registry\JiraClientRegistry;
use PHPUnit\Framework\TestCase;

class JiraClientRegistryTest extends TestCase
{
    private JiraClientRegistry $registry;
    private JiraClientInterface $jiraClient;
    private JiraClientInterface $tracteasyClient;

    protected function setUp(): void
    {
        $this->jiraClient = $this->createMock(JiraClient::class);
        $this->tracteasyClient = $this->createMock(JiraTracteasyClient::class);

        $clients = [
            'jira' => $this->jiraClient,
            'tracteasy' => $this->tracteasyClient,
        ];

        $this->registry = new JiraClientRegistry(new \ArrayIterator($clients));
    }

    public function testGetJiraClient(): void
    {
        $client = $this->registry->get('jira');

        $this->assertSame($this->jiraClient, $client);
    }

    public function testGetTracteasyClient(): void
    {
        $client = $this->registry->get('tracteasy');

        $this->assertSame($this->tracteasyClient, $client);
    }

    public function testGetUnknownClientThrowsException(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Unknown Jira client: unknown');

        $this->registry->get('unknown');
    }
}
