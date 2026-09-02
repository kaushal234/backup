<?php

declare(strict_types=1);

namespace App\Javelo\Notifier;

use App\Entity\Activity\Log;
use App\Entity\Module\Module;
use App\Javelo\DataTransformer\EmailMonitoringDataTransformer;
use App\Repository\Module\ModuleRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class Notifier
{
    private const CREATE_BU_GROUP = 'Create Business Unit Group on Javelo';

    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly EmailMonitoringDataTransformer $dataTransformer,
        private readonly ModuleRepository $moduleRepository,
    ) {
    }

    /**
     * @param Log[] $logs
     */
    public function sendMonitoring(string $subject, array $logs): void
    {
        $tos = $this->getEmailListOnJavelo();

        if (empty($tos)) {
            throw new \Exception('No user to send monitoring email');
        }

        $emailLogs = [];
        foreach ($logs as $log) {
            if (($transformedLog = $this->dataTransformer->transform($log)) === null) {
                continue;
            }
            $emailLogs[] = $transformedLog;
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (string $recipient) => $recipient, $tos))
            ->cc('devteam@tld-america.com')
            ->subject($subject)
            ->htmlTemplate('Emails/Javelo/monitoring.html.twig')
            ->context(['logs' => $emailLogs]);

        $this->mailer->send($email);
    }

    public function sendCreationGroupsRequest(array $missingGroups): void
    {
        $tos = $this->getEmailListOnJavelo();

        if (empty($tos)) {
            throw new \Exception('No user to send creation group request email');
        }

        $missingGroups = \array_slice($missingGroups, 0, 20, true);

        $missingGroupsByDivision = [];
        foreach ($missingGroups as $groupName => $group) {
            $missingGroupsByDivision[$group['division'] ?? 'N/A'][$groupName] = $group['businessUnit'];
        }

        $email = (new TemplatedEmail())
            ->to(...array_map(static fn (string $recipient) => $recipient, $tos))
            ->cc('devteam@tld-america.com')
            ->subject(self::CREATE_BU_GROUP)
            ->htmlTemplate('Emails/Javelo/create_business_unit_group_request.html.twig')
            ->context(['missingGroupsByDivision' => $missingGroupsByDivision]);

        $this->mailer->send($email);
    }

    private function getEmailListOnJavelo(): array
    {
        $module = $this->moduleRepository->findOneBy(['name' => 'Javelo']);

        if (!$module instanceof Module || 'DISABLED' === $module->status) {
            throw new \Exception('Invalid module');
        }
        $tos = [];

        $tos[] = $module->getOperationalOwner()?->getUsername();
        $tos[] = $module->getKeyUser()?->getUsername();
        foreach ($module->getLocalKeyUsers() as $user) {
            $tos[] = $user->getUsername();
        }

        return array_filter($tos);
    }
}
