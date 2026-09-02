<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Directory\People;
use App\Repository\Directory\PeopleRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:export:directory:people:username')]
class ExportDirectoryPeopleUsernameCommand extends Command
{
    private readonly Connection $legacyConnection;
    private readonly PeopleRepository $peopleRepository;

    /**
     * ImportDirectoryPeopleUsernameCommand constructor.
     */
    public function __construct(Connection $legacyConnection, PeopleRepository $peopleRepository)
    {
        parent::__construct();
        $this->setDescription('Export people username property to bdd legacy, people table');
        $this->legacyConnection = $legacyConnection;
        $this->peopleRepository = $peopleRepository;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // add column to table
        $sql = <<<'SQL'
            ALTER TABLE people
                ADD username varchar(255) default '' not null;
            SQL;
        $this->legacyConnection->executeQuery($sql);

        $peoples = $this->peopleRepository->findAll();
        $pg = new ProgressBar($output, \count($peoples));

        /**
         * @var People $people
         */
        foreach ($peoples as $people) {
            $pg->advance();
            if ((null !== $people->getUsername()) && (null !== $people->getLegacyId())) {
                $this->legacyConnection->executeQuery(\sprintf('UPDATE people SET username = "%s" WHERE id= %d', $people->getUsername(), $people->getLegacyId()));
            }
        }
        $pg->finish();

        return 0;
    }
}
