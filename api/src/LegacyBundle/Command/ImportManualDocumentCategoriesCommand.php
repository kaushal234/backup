<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Support\ManualDocumentCategory;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:equipment:manuals:documents:categories')]
class ImportManualDocumentCategoriesCommand extends Command
{
    private readonly EntityManagerInterface $em;
    private readonly Connection $legacyConnection;

    public function __construct(
        EntityManagerInterface $em,
        Connection $legacyConnection
    ) {
        parent::__construct();
        $this->setDescription('Import manuals_diag categories from legacy');
        $this->em = $em;
        $this->legacyConnection = $legacyConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sql = <<<'SQL'
                    SELECT distinct category
                    FROM manuals_diag
                    WHERE category <> ''
                    AND category <> 'Body/Chassis'
                    AND category <> 'Lifting/Scissors System'
            SQL;
        $stmt = $this->legacyConnection->executeQuery($sql);

        if ($stmt->rowCount() <= 0) {
            return Command::SUCCESS;
        }
        $progress = new ProgressBar($output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
        $progress->start($stmt->rowCount());

        $operationCounter = 0;
        while ($data = $stmt->fetchAssociative()) {
            if ('' !== $data['category']) {
                $category = new ManualDocumentCategory();
                $category->name = $data['category'];
                $this->em->persist($category);
                ++$operationCounter;
            }
            $progress->advance();
        }
        $this->em->flush();
        $progress->finish();

        $output->writeln('');
        $output->writeln(\sprintf('<info>Imported <comment>%d</comment> Categories </info>', $operationCounter));

        return Command::SUCCESS;
    }
}
