<?php

declare(strict_types=1);

namespace App\Command\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\Location;
use App\Entity\Service\TechnicianOnCall;
use App\Factory\SanitizedEmailListFactory;
use App\Repository\Directory\PeopleRepository;
use App\Util\DateUtil;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(name: 'api:toc:late_customer_updates_report')]
class TechnicianOnCallLateCustomerUpdatesReportCommand extends Command
{
    private const DAYS_SINCE_LAST_EXTERNAL_LOG_THRESHOLD = 5;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface $mailer,
        private readonly TranslatorInterface $translator,
        private readonly IriConverterInterface $iriConverter,
        private readonly PeopleRepository $peopleRepository,
        private readonly SanitizedEmailListFactory $sanitizedEmailListFactory,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locationRepository = $this->entityManager->getRepository(Location::class);
        $locations = $locationRepository->findBy(['capability.sso' => true]);

        if (empty($locations)) {
            $output->writeln('No SSO locations found.');

            return Command::SUCCESS;
        }

        $tocRepository = $this->entityManager->getRepository(TechnicianOnCall::class);
        $bySso = [];

        foreach ($locations as $location) {
            /** @var Location $location */
            $tocs = $tocRepository->createQueryBuilder('t')
                ->addSelect('technician', 'equipmentRecord', 'customer', 'airport', 'unitOperationalStatus')
                ->leftJoin('t.technician', 'technician')
                ->leftJoin('t.equipmentRecord', 'equipmentRecord')
                ->leftJoin('t.customer', 'customer')
                ->leftJoin('t.airport', 'airport')
                ->leftJoin('t.unitOperationalStatus', 'unitOperationalStatus')
                ->where('t.status IN (:opened)')
                ->andWhere('t.salesOrganisationService = :location')
                ->setParameter('opened', TechnicianOnCall::OPENED_STATUSES)
                ->setParameter('location', $location)
                ->getQuery()
                ->getResult();

            if (empty($tocs)) {
                $output->writeln(\sprintf('No open TOCs found for %s, skipping.', $location->getName()));
                continue;
            }

            [$lateTocs, $lastExternalLogAtByTocId, $daysSinceLastExternalLogByTocId] = $this->filterLateTocs($tocs);

            if (empty($lateTocs)) {
                $output->writeln(\sprintf('No late TOCs found for %s.', $location->getName()));
                continue;
            }

            $bySso[] = [
                'sso' => $location,
                'tocs' => $lateTocs,
                'lastExternalLogAtByTocId' => $lastExternalLogAtByTocId,
                'daysSinceLastExternalLogByTocId' => $daysSinceLastExternalLogByTocId,
            ];

            $recipients = $this->findLocationRecipients($location);

            try {
                $this->mailer->send(
                    (new TemplatedEmail())
                        ->from('noreply@tld-gse.com')
                        ->to(...$recipients)
                        ->subject($this->translator->trans('toc.subject.toc_late_customer_updates_report', ['%sso%' => $location->getName()], 'emails'))
                        ->htmlTemplate('Emails/Service/TechnicianOnCall/toc_late_customer_updates_report.html.twig')
                        ->context([
                            'salesOrganisationService' => $location,
                            'tocs' => $lateTocs,
                            'lastExternalLogAtByTocId' => $lastExternalLogAtByTocId,
                            'daysSinceLastExternalLogByTocId' => $daysSinceLastExternalLogByTocId,
                        ])
                );

                $output->writeln(\sprintf(
                    'Sent late customer updates report for %s (%d late TOCs)',
                    $location->getName(),
                    \count($lateTocs)
                ));
            } catch (\Throwable $e) {
                $output->writeln(\sprintf(
                    'Failed to send late customer updates report for %s: %s',
                    $location->getName(),
                    $e->getMessage()
                ));
            }
        }

        $this->sendExecutiveSummary($bySso, $output);

        return Command::SUCCESS;
    }

    /**
     * @param array<array{sso: Location, tocs: array<TechnicianOnCall>, lastExternalLogAtByTocId: array<int, \DateTimeInterface>, daysSinceLastExternalLogByTocId: array<int, int>}> $bySso
     */
    private function sendExecutiveSummary(array $bySso, OutputInterface $output): void
    {
        if (empty($bySso)) {
            $output->writeln('No late TOCs found for any SSO, skipping executive summary.');

            return;
        }

        $recipients = $this->sanitizedEmailListFactory->buildCleanEmailAddressList(
            $this->peopleRepository->findGroupsMembers(['ROLE_TCEO', 'ROLE_TCOO'])
        );

        try {
            $this->mailer->send(
                (new TemplatedEmail())
                    ->from('noreply@tld-gse.com')
                    ->to(...$recipients)
                    ->subject($this->translator->trans('toc.subject.toc_late_customer_updates_report_all_sso', [], 'emails'))
                    ->htmlTemplate('Emails/Service/TechnicianOnCall/toc_late_customer_updates_report_all_sso.html.twig')
                    ->context(['bySso' => $bySso])
            );

            $output->writeln(\sprintf('Sent executive summary of late customer updates (%d SSO)', \count($bySso)));
        } catch (\Throwable $e) {
            $output->writeln(\sprintf('Failed to send executive summary of late customer updates: %s', $e->getMessage()));
        }
    }

    /**
     * @param array<TechnicianOnCall> $tocs
     *
     * @return array{0: array<TechnicianOnCall>, 1: array<int, \DateTimeInterface>, 2: array<int, int>}
     */
    private function filterLateTocs(array $tocs): array
    {
        $iriByToc = [];
        foreach ($tocs as $toc) {
            $iriByToc[$toc->getId()] = $this->iriConverter->getIriFromResource($toc);
        }

        $lastExternalLogAtByIri = $this->findLastExternalLogEntries(array_values($iriByToc));

        $lateTocs = [];
        $lastExternalLogAtByTocId = [];
        $daysSinceLastExternalLogByTocId = [];

        foreach ($tocs as $toc) {
            $lastExternalLogAt = $lastExternalLogAtByIri[$iriByToc[$toc->getId()]] ?? null;
            $referenceDate = $lastExternalLogAt ?? $toc->createdAt;
            $daysSinceLastExternalLog = DateUtil::diff($referenceDate, null)->days;

            if ($daysSinceLastExternalLog <= self::DAYS_SINCE_LAST_EXTERNAL_LOG_THRESHOLD) {
                continue;
            }

            $lateTocs[] = $toc;
            $lastExternalLogAtByTocId[$toc->getId()] = $lastExternalLogAt;
            $daysSinceLastExternalLogByTocId[$toc->getId()] = $daysSinceLastExternalLog;
        }

        return [$lateTocs, $lastExternalLogAtByTocId, $daysSinceLastExternalLogByTocId];
    }

    /**
     * @param list<string> $iris
     *
     * @return array<string, \DateTimeInterface> last public comment createdAt, keyed by TOC IRI
     */
    private function findLastExternalLogEntries(array $iris): array
    {
        if ([] === $iris) {
            return [];
        }

        $comments = $this->entityManager->getRepository(Comment::class)
            ->createQueryBuilder('c')
            ->where('c.resource IN (:iris)')
            ->andWhere('c.public = :public')
            ->setParameter('iris', $iris)
            ->setParameter('public', true)
            ->getQuery()
            ->getResult();

        $lastByIri = [];
        /** @var Comment $comment */
        foreach ($comments as $comment) {
            $resource = $comment->getResource();
            $createdAt = $comment->getCreatedAt();

            if (!isset($lastByIri[$resource]) || $createdAt > $lastByIri[$resource]) {
                $lastByIri[$resource] = $createdAt;
            }
        }

        return $lastByIri;
    }

    private function findLocationRecipients(Location $sso): array
    {
        return $this->sanitizedEmailListFactory->buildCleanEmailAddressList(
            $this->peopleRepository->findGroupsMembers(['ROLE_CSM_TOC_LATE_NOT', 'ROLE_EVP'], $sso)
        );
    }
}
