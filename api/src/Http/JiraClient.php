<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AutoconfigureTag(name: 'jira.client', attributes: ['key' => 'jira'])]
class JiraClient extends AbstractJiraClient
{
    public function getFixturesDirectory(): string
    {
        return $this->projectDir.'/tests/fixtures/jira/http';
    }

    #[Required]
    public function setClient(HttpClientInterface $jiraClient): void
    {
        $this->httpClient = $jiraClient;
    }
}
