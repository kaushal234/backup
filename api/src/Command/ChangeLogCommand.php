<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\Directory\People;
use App\Entity\Module\ChangeLog;
use App\Entity\Module\Module;
use App\Http\GitlabClient;
use App\Mailer\Module\ChangelogPoolMailer;
use App\Repository\Directory\PeopleRepository;
use App\Repository\Module\ModuleRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:changelog:synchronize')]
class ChangeLogCommand extends Command
{
    /**
     * @var string
     */
    final public const COMMIT_PATTERN = '/
        ^(?P<type>\w+)          # MANDATORY: type at the start
        (?:\((?P<scope>.+?)\))?     # OPTIONAL:scope inside parentheses
        :                           # MANDATORY: Having a colon after the scope
        (?P<message>.*?)        # everything after module until Module:
        (?:Module\s*:\s*(?P<module>.*?)(?=Description\s*:|Refs\s*:|$))?   # OPTIONAL: Module: field
        Description\s*:\s*(?P<description>.*?)(?=Refs\s*:|$)   # MANDATORY : Description field
        (?:Refs\s*:\s*(?P<refs>.*))?     # OPTIONAL:  Refs field
        $/x';

    public function __construct(
        private readonly GitlabClient $gitlabClient,
        private readonly ManagerRegistry $registry,
        private readonly ChangelogPoolMailer $mailer
    ) {
        parent::__construct();
        $this->setDescription('Fetch last commits from Gitlab');
        $this->addArgument('repositoryIds', InputArgument::IS_ARRAY, 'Gitlab repositories ids');
        $this->addOption('since', 's', InputOption::VALUE_OPTIONAL, 'Date since', 'yesterday 00:00:00');
        $this->addOption('until', 'u', InputOption::VALUE_OPTIONAL, 'Date until', 'today 00:00:00');
        $this->addOption('show', 'show', InputOption::VALUE_OPTIONAL, 'Show the commit message from the Gitlab', false);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $entityManager = $this->registry->getManager();

        /** @var PeopleRepository $peopleRepository */
        $peopleRepository = $entityManager->getRepository(People::class);
        /** @var ModuleRepository $moduleRepository */
        $moduleRepository = $entityManager->getRepository(Module::class);
        $changeLogRepository = $entityManager->getRepository(ChangeLog::class);

        $commits = [];

        /** @var string $since */
        $since = $input->getOption('since');
        $since = new \DateTime($since);
        /** @var string $until */
        $until = $input->getOption('until');
        $until = new \DateTime($until);

        /** @var array $repositories */
        $repositories = $input->getArgument('repositoryIds');
        foreach ($repositories as $project) {
            $projectCommits = $this->gitlabClient->getCommits(
                (int) $project,
                $since,
                $until
            );
            // Merge numerically indexed arrays correctly across multiple repositories
            $commits = array_merge($commits, $projectCommits);
        }

        $memory = [];
        foreach ($commits as $commit) {
            $matches = [];
            $message = preg_replace("/\r|\n/", '', (string) $commit['message']);

            if ($input->getOption('show')) {
                $output->writeln(\sprintf('<comment><fg=cyan>Commit Message : "%s"</comment>', $message));
            }

            if (!preg_match_all(self::COMMIT_PATTERN, $message, $matches, \PREG_SET_ORDER)) {
                $output->writeln(\sprintf('<comment>Skipped commit -> could not match preg. Commit ID : "%s"
                </comment>', $commit['id']));
                continue;
            }

            $infos = current($matches);

            // Skip if the description is empty
            if (empty(mb_trim($infos['description']))) {
                $output->writeln(\sprintf('<comment>Skipped commit -> could not find description. Commit ID : "%s"
                </comment>', $commit['id']));
                continue;
            }

            $author = $peopleRepository->findOneBy(['email' => $commit['author_email']]);

            // Skip if Author is not found
            if (!$author instanceof People) {
                $output->writeln(\sprintf('<comment>Skipped commit -> could not find Author. Commit ID : "%s"
                </comment>', $commit['id']));
                continue;
            }

            $refsTickets = mb_trim($infos['refs'] ?? '');
            if ('' === $refsTickets) {
                $infos['ticket'] = '';
            } else {
                preg_match('/TTS\s*#(\d+)/', $refsTickets, $matches);
                $infos['ticket'] = $matches[1] ?? '';
                // Skipping the commit because the Commit has non TTS ticket
                if ('' === $infos['ticket']) {
                    $output->writeln(\sprintf('<comment>Skipped commit -> could not find any TTS ticket in refs. Tickets: (%s). Commit ID : "%s"
                    </comment>', $refsTickets, $commit['id']));
                    continue;
                }
            }
            $message = mb_trim($infos['description']);

            $index = $infos['module'].$message;

            if (\array_key_exists($index, $memory)) {
                $output->writeln(\sprintf('<comment>Skipped commit -> duplicate commit ID. Commit ID : "%s"
                </comment>', $commit['id']));
                continue;
            }

            $memory[$index] = true;

            $module = $moduleRepository->findByName($infos['module']);

            if (!$module instanceof Module) {
                $infos['description'] = !empty(mb_trim($infos['module'])) ?
                    \sprintf('[%s] : %s', $infos['module'], $infos['description']) :
                    $infos['description'];
            }

            $conflictingHash = $changeLogRepository->findOneBy(['hash' => $commit['id']]);

            if (null !== $conflictingHash) {
                $output->writeln(\sprintf('<comment>Skipped commit -> conflicting hash Id . Commit ID : "%s"
                </comment>', $commit['id']));
                continue;
            }
            $changelog = (new ChangeLog())
                ->setHash($commit['id'])
                ->setModule($module)
                ->setAuthor($author)
                ->setDate(new \DateTime($commit['created_at']))
                ->setMessage($infos['description'])
                ->setType($infos['type'])
                ->setTicket('' !== $infos['ticket'] ? (int) $infos['ticket'] : null)
            ;

            $entityManager->persist($changelog);
            $entityManager->flush();

            $this->mailer->addChangelog($changelog);

            $output->writeln(\sprintf('<info>Imported commit "%s , Commit ID: %s"
            </info>', $infos['description'], $commit['id']));
        }

        $this->mailer->send('changelog.subject', 'Emails/Module/changelog_moo.html.twig');

        return 0;
    }
}
