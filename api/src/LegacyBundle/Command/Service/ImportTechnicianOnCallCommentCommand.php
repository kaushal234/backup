<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Doctrine\EventListener\EntityChangeListener;
use App\Doctrine\Utils\ListenerManager;
use App\Entity\Activity\Comment;
use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Command\Helper\EntityCacheHelper;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\SanitationHelper;
use LegacyBundle\Doctrine\EventListener\PersistenceSubscriber;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:technician_on_call:comment', description: 'Import technicians on call comment from legacy')]
class ImportTechnicianOnCallCommentCommand extends Command
{
    use TechnicianOnCallListOptionTrait;

    private EntityCacheHelper $peopleCache;
    private EntityCacheHelper $technicianOnCallCache;

    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $entityManager,
        private readonly EntityCacheHelperFactory $cacheFactory,
        private readonly SanitationHelper $sanitationHelper,
        private readonly IriConverterInterface $iriConverter,
        private readonly ListenerManager $listenerManager,
    ) {
        parent::__construct();
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $events = $this->entityManager->getClassMetadata(Comment::class)->lifecycleCallbacks;
        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks([]);
        $this->listenerManager->removeListener($this->entityManager, [EntityChangeListener::class, PersistenceSubscriber::class]);

        $this->peopleCache = $this->cacheFactory->createEntityCache(People::class, 'legacyId');
        $this->technicianOnCallCache = $this->cacheFactory->createEntityCache(TechnicianOnCall::class, 'legacyId');

        $legacyComments = $this->getLegacyComments();

        if (empty($legacyComments)) {
            $output->writeln('<info>No comments to import</info>');

            return Command::SUCCESS;
        }

        $this->registerComments($legacyComments, $output);

        $this->entityManager->flush();

        $this->entityManager->getClassMetadata(Comment::class)->setLifecycleCallbacks($events);

        return Command::SUCCESS;
    }

    private function getLegacyComments(): array
    {
        $sql = <<<SQL
                SELECT
                    id,
                    parent_id AS parent_id,
                    mod_logs.comment AS comment,
                    NULL AS recipients,
                    NULL AS cc,
                    NULL AS bcc,
                    mod_logs.date AS date,
                    mod_logs.poster AS poster,
                    mod_logs.log_num AS log_num 
                FROM
                    mod_logs
                WHERE mod_logs.module LIKE 'TOC'
                AND parent_id IN ({$this->tocIdList})
                AND poster != 0
                ORDER BY id DESC
            SQL;

        return $this->legacyConnection->executeQuery($sql)->fetchAllAssociative();
    }

    private function registerComments(array $legacyComments, OutputInterface $output): void
    {
        foreach ($legacyComments as $legacyComment) {
            $technicianOnCall = $this->technicianOnCallCache->fetch((string) $legacyComment['parent_id']);
            $comment = new Comment();

            if (null === $technicianOnCall) {
                $output->writeln(\sprintf('<error>Comment #%s not imported because TOC linked not found</error>', $legacyComment['id']));
                continue;
            }

            $user = $this->peopleCache->fetch((string) $legacyComment['poster']);
            if (null === $user) {
                $output->writeln(\sprintf('<error>Comment #%s not imported because Poster not found</error>', $legacyComment['id']));
                continue;
            }

            $comment
                ->setMessage(mb_trim($this->sanitationHelper->parse($legacyComment['comment'])))
                ->setCreatedAt(new \DateTime($legacyComment['date']))
                ->setUpdatedAt(new \DateTime($legacyComment['date']))
                ->setResource($this->iriConverter->getIriFromResource($technicianOnCall))
                ->setUser($this->peopleCache->fetch((string) $legacyComment['poster']))
                ->setLegacyId($legacyComment['id'])
                ->setPublic(10 === (int) $legacyComment['log_num'])
            ;

            $this->entityManager->persist($comment);

            $output->writeln(\sprintf('<info>Comment #%s imported</info>', $legacyComment['id']));
        }
    }
}
