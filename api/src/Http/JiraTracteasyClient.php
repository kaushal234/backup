<?php

declare(strict_types=1);

namespace App\Http;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\Service\Attribute\Required;

#[AutoconfigureTag(name: 'jira.client', attributes: ['key' => 'tracteasy'])]
class JiraTracteasyClient extends AbstractJiraClient
{
    public function getFixturesDirectory(): string
    {
        return $this->projectDir.'/tests/fixtures/jiraTracteasy/http';
    }

    #[Required]
    public function setClient(HttpClientInterface $jiraTracteasyClient): void
    {
        $this->httpClient = $jiraTracteasyClient;
    }
}
