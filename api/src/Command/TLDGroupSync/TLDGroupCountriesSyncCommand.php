<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use App\Entity\Country;
use App\Repository\CountryRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:group:sync:countries')]
class TLDGroupCountriesSyncCommand extends Command
{
    private readonly CountryRepository $countryRepository;

    private readonly Connection $wordpressConnection;

    public function __construct(CountryRepository $countryRepository, Connection $wordpressConnection)
    {
        parent::__construct();
        $this->setDescription('Sync Countries to TLD Group Wordpress database');

        $this->countryRepository = $countryRepository;
        $this->wordpressConnection = $wordpressConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Country[] $countries */
        $countries = $this->countryRepository->findPublic();

        $q = $this->wordpressConnection->getDatabasePlatform()->getTruncateTableSQL('tld_extranet_country');
        $this->wordpressConnection->executeStatement($q);

        $pg = new ProgressBar($output, \count($countries));

        $i = 1;
        foreach ($countries as $country) {
            $pg->advance();

            $qb = $this->wordpressConnection->createQueryBuilder();

            $qb
                ->insert('tld_extranet_country')
                ->setValue('id', ':id')
                ->setValue('name', ':name')
                ->setParameters([
                    'id' => $i,
                    'name' => $country->getName(),
                ])
            ;

            $this->wordpressConnection->executeQuery($qb->getSQL(), $qb->getParameters());
            ++$i;
        }
        $pg->finish();

        return 0;
    }
}
