<?php

declare(strict_types=1);

namespace LegacyBundle\Command\MIS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\MIS\TroubleTicket\TroubleTicket;
use App\Entity\MIS\TroubleTicket\Type;
use App\Entity\Module\Module;
use App\Repository\AclRepository;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\ImportHelper;
use LegacyBundle\Command\Helper\SanitationHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:trouble_ticket', description: 'Import trouble ticket from legacy')]
class ImportTroubleTicketCommand extends Command
{
    public const CLASSIC_MAPPING = [
        'AGILE' => [
            'module' => 'Agile',
            'option' => 'I cannot proceed',
        ],
        'Birst Reports' => [
            'module' => 'Birst',
            'option' => 'New feature proposal',
        ],
        'CELL PHONE' => [
            'module' => 'Cell Phone',
            'option' => 'IT Purchase request',
        ],
        'COMMUNICATION' => [
            'module' => 'ALVEST',
            'option' => 'New feature proposal',
        ],
        'CPQ - Infor' => [
            'module' => 'CPQ',
            'option' => 'More permission needed',
        ],
        'DESK/OFFICE PHONE' => [
            'module' => 'Telephony',
            'option' => 'IT Purchase request',
        ],
        'EMAIL' => [
            'module' => 'Email',
            'option' => 'I cannot proceed',
        ],
        'ERP - EAM/SUN' => [
            'module' => 'EAM',
            'option' => 'I cannot proceed',
        ],
        'Factory Track' => [
            'module' => 'Factory Track',
            'option' => 'I cannot proceed',
        ],
        'HARDWARE' => [
            'module' => 'Desktop',
            'option' => 'Other',
        ],
        'KELIO' => [
            'module' => 'KELIO',
            'option' => 'I cannot proceed',
        ],
        'LINK FMS' => [
            'module' => 'LINK',
            'option' => 'I cannot proceed',
        ],
        'NETWORK' => [
            'module' => 'Ethernet',
            'option' => 'I cannot proceed',
        ],
        'PURCHASING REQUEST' => [
            'module' => 'Desktop',
            'option' => 'IT Purchase request',
        ],
        'SECURITY' => [
            'module' => 'Email',
            'option' => 'Security - Not Urgent',
        ],
        'SHOPFLOOR TABLET' => [
            'module' => 'Shopfloor tab',
            'option' => 'I cannot proceed',
        ],
        'SOFTWARE' => [
            'module' => 'Other',
            'option' => 'Other',
        ],
        'Solidworks/PDMworks/SeeElec' => [
            'module' => 'SLD',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE' => [
            'module' => 'ALVEST',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE, eQuotes' => [
            'module' => 'EQUO',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE, eVendor' => [
            'module' => 'Evendor',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE, DMS' => [
            'module' => 'DMS',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE, Customer ePARTS' => [
            'module' => 'Rhythm/eParts',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE, Customer EXTRANET' => [
            'module' => 'Extranet',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE, SHOPFLOOR' => [
            'module' => 'Shopfloor',
            'option' => 'I cannot proceed',
        ],
        'WEBSITE, INTRANET' => [
            'module' => 'TASK',
            'option' => 'I cannot proceed',
        ],
    ];

    public const LN_MAPPING = [
        'ALL_DOMAINS' => [
            'module' => 'Accounting',
            'option' => 'New feature proposal',
        ],
        'tld-america.com' => [
            'module' => 'Supply chain',
            'option' => 'New feature proposal',
        ],
        'aerospecialties.com' => [
            'module' => 'Manufacturing',
            'option' => 'New feature proposal',
        ],
        'alvest.fr' => [
            'module' => 'Engineering',
            'option' => 'New feature proposal',
        ],
        'lebrun.eu' => [
            'module' => 'Supply chain',
            'option' => 'New feature proposal',
        ],
        'powervamp.com' => [
            'module' => 'Supply chain',
            'option' => 'New feature proposal',
        ],
        'tld-asia.com' => [
            'module' => 'Manufacturing',
            'option' => 'I cannot proceed',
        ],
        'tld-europe.com' => [
            'module' => 'Supply chain',
            'option' => 'New feature proposal',
        ],
        'tld-japan.com' => [
            'module' => 'Supply chain',
            'option' => 'New feature proposal',
        ],
        'tld-meai.com' => [
            'module' => 'Supply chain',
            'option' => 'New feature proposal',
        ],
    ];

    public const TYPE_MAPPING = [
        'bugfix' => 'I cannot proceed',
        'enhancement' => 'New feature proposal',
        'question' => 'More training needed',
        'feature' => 'New feature proposal',
        'permission' => 'More permission needed',
        'mistake' => 'Other',
        'virus' => 'Security - High Attention',
        'authorization' => 'Security - Not Urgent',
        'training' => 'More training needed',
        'error' => 'I cannot proceed',
    ];

    public const REASONS = [
        'Propose an enhancement of an existing feature' => 'New feature proposal',
        'Propose a new feature to the module' => 'New feature proposal',
        'Fix a bug' => 'I cannot proceed',
        "Raise a functional question about module which is not covered in module's DMS" => 'More training needed',
        'Request permission to access a restricted feature ' => 'More permission needed',
        'Request to change a data value following mistake or module miss-use' => 'Other',
        'I OPENED a VIRUS' => 'Security - High Attention',
        'Other security subjects that do not require immediate incident response' => 'Security - Not Urgent',
        'I am missing permissions/access to a specific session/process' => 'More permission needed',
        'I require additional training/guidance in order to proceed' => 'More training needed',
        'I received an Error Message which is preventing me from proceeding' => 'I cannot proceed',
    ];

    public function __construct(
        private readonly ImportHelper $helper,
        private readonly Connection $legacyConnection,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly AclRepository $aclRepository,
        private readonly IriConverterInterface $iriConverter,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $moduleCacheByLegacyId = $this->cacheFactory->createEntityCache(Module::class, 'legacyId');
        $moduleCacheByName = $this->cacheFactory->createEntityCache(Module::class, 'name');
        $typeCache = $this->cacheFactory->createEntityCache(Type::class, 'description');
        $troubleTicketCache = $this->cacheFactory->createEntityCache(TroubleTicket::class, 'legacyId');

        $sql = <<<'SQL'
            SELECT tasks.id, tasks.assignor, tasks.status, tasks.date, tasks.task, tasks.assignee, tasks.due_date, tasks.ifactor, tasks.cat, tasks.reason, tasks.jira_issue, tasks.ticket_module_id, mis_tts.category, mis_tts.domain
            FROM tasks
            LEFT JOIN mis_tts ON tasks.parent_id = mis_tts.id
            WHERE tasks.module = 'TTS' AND tasks.status IN ('IN PROGRESS', 'OPEN') AND mis_tts.status = 'QUEUE'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $this->helper->disableValidation();
        $this->helper->progressiveImport(
            $output, $stmt, TroubleTicket::class, 'legacyId', 'id',
            function (TroubleTicket $troubleTicket, array $data) use ($moduleCacheByLegacyId, $moduleCacheByName, $peopleCache, $typeCache) {
                if ('BAAN' === $data['category']) {
                    throw new \InvalidArgumentException('TTS not imported');
                }
                $troubleTicket->indiceFactor = null !== $data['ifactor'] ? \sprintf('IF %s', $data['ifactor']) : null;
                $troubleTicket->shortDescription = mb_trim($this->sanitationHelper->parse(mb_substr($data['task'], 0, 100)));
                $troubleTicket->description = mb_trim($this->sanitationHelper->parse($data['task']));
                $troubleTicket->dueDate = new \DateTime($data['due_date']);
                $troubleTicket->createdAt = new \DateTime($data['date']);

                $troubleTicket->createdBy = $peopleCache->fetch((string) $data['assignor']);

                $type = null;
                if (null !== $data['reason'] && '' !== $data['reason']) {
                    /** @var Type $type */
                    $type = $typeCache->fetch(self::TYPE_MAPPING[$data['reason']]);
                }

                if (null === $type) {
                    foreach (array_keys(self::REASONS) as $reason) {
                        if (null !== $type) {
                            continue;
                        }
                        if (str_contains($data['task'], $reason)) {
                            /** @var Type $type */
                            $type = $typeCache->fetch(self::REASONS[$reason]);
                        }
                    }
                }

                if (null === $type) {
                    if ('WEBSITE, INTRANET' !== $data['category']) {
                        $mapping = 'ERP - Infor LN' === $data['category'] ? self::LN_MAPPING : self::CLASSIC_MAPPING;
                        $key = 'ERP - Infor LN' === $data['category'] ? 'domain' : 'category';
                        /** @var Type $type */
                        $type = $typeCache->fetch($mapping[$data[$key]]['option']);
                    }
                }

                if (null === $type) {
                    /** @var Type $type */
                    $type = $typeCache->fetch('I cannot proceed');
                }
                $troubleTicket->type = $type;

                $module = null;
                if (null !== $data['ticket_module_id']) {
                    /** @var Module $module */
                    $module = $moduleCacheByLegacyId->fetch((string) $data['ticket_module_id']);
                }

                if (null === $module) {
                    $mapping = 'ERP - Infor LN' === $data['category'] ? self::LN_MAPPING : self::CLASSIC_MAPPING;
                    $key = 'ERP - Infor LN' === $data['category'] ? 'domain' : 'category';
                    /** @var Module $module */
                    $module = $moduleCacheByName->fetch($mapping[$data[$key]]['module']);
                }

                $troubleTicket->module = $module;

                if (null !== $data['jira_issue']) {
                    $troubleTicket->jiraIssueNumber = \sprintf('SP-%s', $data['jira_issue']);
                }

                $assignee = $peopleCache->fetch((string) $data['assignee']);
                $status = TroubleTicket::PENDING;
                if (null !== $assignee) {
                    if (Type::INCIDENT === $troubleTicket->type->type && !$this->aclRepository->userHasRoles($assignee, ['GG_MIS'])) {
                        $status = TroubleTicket::SOLUTION_PROPOSED;
                        $troubleTicket->solutionProposedAt = new \DateTime();
                    }

                    if (Type::REQUEST === $troubleTicket->type->type && null !== $module) {
                        $status = $assignee === $module->getOperationalOwner() || $assignee === $module->getKeyUser() ? TroubleTicket::PENDING_MOO : TroubleTicket::AWAITING_USER_MOO;
                    }

                    if ($this->aclRepository->userHasRoles($assignee, ['GG_MIS']) && TroubleTicket::AWAITING_USER_MOO !== $status) {
                        $assignee = null;
                    }
                }

                $troubleTicket->setStatus($status);
                $troubleTicket->assignee = $assignee;
            }
        );

        // Import TTS Comments
        $sql = <<<'SQL'
                SELECT tasks_comments.id, tasks_comments.parent_id, tasks_comments.date, tasks_comments.poster, tasks_comments.comment
                FROM tasks_comments
                LEFT JOIN tasks ON tasks.id = tasks_comments.parent_id
                LEFT JOIN mis_tts ON tasks.parent_id = mis_tts.id
                WHERE tasks.module = 'TTS' AND tasks.status IN ('IN PROGRESS', 'OPEN') AND mis_tts.status = 'QUEUE'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);

        $this->helper->disableValidation();
        $this->helper->setBatchSize(10_000);

        $this->helper->progressiveImport(
            $output, $stmt, Comment::class, 'legacyId', 'id',
            function (Comment $comment, array $data) use ($peopleCache, $troubleTicketCache, $output) {
                $troubleTicket = $troubleTicketCache->fetch((string) $data['parent_id']);

                if (null === $troubleTicket) {
                    $output->writeln(\sprintf('<error>Comment #%s not imported because TTS linked not found</error>', $data['id']));
                    throw new \InvalidArgumentException('TTS not found');
                }

                $user = $peopleCache->fetch((string) $data['poster']);
                if (null === $user) {
                    $output->writeln(\sprintf('<error>Comment #%s not imported because Poster not found</error>', $data['id']));
                    throw new \InvalidArgumentException('Poster not found');
                }

                $comment
                    ->setMessage(mb_trim($this->sanitationHelper->parse($data['comment'])))
                    ->setCreatedAt(new \DateTime($data['date']))
                    ->setUpdatedAt(new \DateTime($data['date']))
                    ->setResource($this->iriConverter->getIriFromResource($troubleTicket))
                    ->setUser($peopleCache->fetch((string) $data['poster']))
                ;
            }
        );

        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);

        return Command::SUCCESS;
    }
}
