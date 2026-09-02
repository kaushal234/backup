<?php

declare(strict_types=1);

namespace App\Command\MIS;

use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Http\JiraClient;
use App\Notifier\MIS\TroubleTicket\TroubleTicketNotifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'api:mis:jira_closed', description: 'Notify Team for Jira issues done for TTS opened')]
class NotifyTroubleTicketDoneInJiraCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JiraClient $client,
        private readonly TroubleTicketNotifier $notifier,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repository = $this->entityManager->getRepository(TroubleTicket::class);

        foreach (['SP' => 'devteam@tld-america.com', 'LN' => 'ln_admins@tld-group.com'] as $project => $email) {
            $troubleTicketToNotify = [];
            $token = null;
            $maxResults = 100;

            do {
                $query = [
                    'jql' => \sprintf('project = %s ORDER BY created ASC', $project),
                    'maxResults' => $maxResults,
                    'fields' => 'status',
                    'fetchIssueKeys' => 'true',
                ];

                // token-based pagination https://developer.atlassian.com/cloud/jira/platform/rest/v3/api-group-issue-search/#api-rest-api-3-search-jql-get
                if (null !== $token) {
                    $query['nextPageToken'] = $token;
                }

                $data = json_decode(
                    $this->client->doRequest('search/jql', null, ['query' => $query])->getContent(),
                    true,
                    512,
                    \JSON_THROW_ON_ERROR
                );
                $issues = $data['issues'] ?? [];

                foreach ($issues as $issue) {
                    if (!\in_array($issue['fields']['status']['name'], ['Done', 'To validate in Prod', 'To validate in staging'], true)) {
                        continue;
                    }

                    $troubleTicket = $repository->findOneBy(['jiraIssueNumber' => $issue['key']]);
                    if (null === $troubleTicket) {
                        continue;
                    }

                    if (\in_array($troubleTicket->getStatus(), [TroubleTicket::SOLUTION_PROPOSED, ...TroubleTicket::CLOSED_STATUSES], true)) {
                        continue;
                    }

                    $troubleTicketToNotify[] = $troubleTicket;
                }
                $token = $data['nextPageToken'] ?? null;
                $isLast = $data['isLast'] ?? true;
            } while (false === $isLast);

            if (0 === \count($troubleTicketToNotify)) {
                continue;
            }

            $this->notifier->sendJiraReminder($troubleTicketToNotify, $email);
        }

        return Command::SUCCESS;
    }
}
