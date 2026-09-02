<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Activity\Comment;
use App\Entity\Service\CustomerServiceRecord\AbstractCustomerServiceRecord;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:service:csr-solution')]
class ImportCustomerServiceRecordSolutionCommand extends Command
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $entityManager,
        private readonly IriConverterInterface $iriConverter,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Import CSR
        $sql = <<<'SQL'
            SELECT *
            FROM mod_faq
            WHERE module = 'CSR'
            SQL;

        $stmt = $this->legacyConnection->executeQuery($sql);
        $modFaq = $stmt->fetchAllAssociative();
        $modFaqCSRIds = array_column($modFaq, 'parent_id');

        $customerServiceRecords = $this->entityManager->getRepository(AbstractCustomerServiceRecord::class)->findBy(['legacyId' => $modFaqCSRIds]);
        $output->writeln([
            \sprintf('%d Mod FAQ found ', \count($modFaqCSRIds)),
            \sprintf('%d CSR found', \count($customerServiceRecords)),
            '============',
            '',
        ]);
        $progressBar = new ProgressBar($output, \count($customerServiceRecords));
        $progressBar->start();
        $count = 0;

        foreach ($customerServiceRecords as $customerServiceRecord) {
            $modFaqKey = array_search($customerServiceRecord->getId(), $modFaqCSRIds, true);

            $text = <<<EOF
                Text recorded at CSR completion:
                <ul>
                    <li>Symptom : {$modFaq[$modFaqKey]['symptom']}</li>
                    <li>Problem : {$modFaq[$modFaqKey]['problem']}</li>
                    <li>Solution : {$modFaq[$modFaqKey]['solution']}</li>
                </ul>
                EOF;

            $comment = (new Comment())
                ->setMessage($text)
                ->setCreatedAt($customerServiceRecord->completedAt ?? new \DateTime())
                ->setResource($this->iriConverter->getIriFromResource($customerServiceRecord))
            ;
            $this->entityManager->persist($comment);
            ++$count;
            $progressBar->advance();

            if ($count > 1000) {
                $this->entityManager->flush();
                $count = 0;
            }
        }

        $this->entityManager->flush();
        $progressBar->finish();

        return Command::SUCCESS;
    }
}
