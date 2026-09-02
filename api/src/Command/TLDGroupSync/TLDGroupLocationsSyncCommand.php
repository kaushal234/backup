<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use App\Repository\Directory\LocationRepository;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Exception\MissingResourceException;

#[AsCommand(name: 'tld:group:sync:locations')]
class TLDGroupLocationsSyncCommand extends Command
{
    private readonly LocationRepository $locationRepository;
    private readonly Connection $wordpressConnection;

    public function __construct(LocationRepository $locationRepository, Connection $wordpressConnection)
    {
        parent::__construct();
        $this->setDescription('Sync Locations to TLD Group Wordpress database');

        $this->locationRepository = $locationRepository;
        $this->wordpressConnection = $wordpressConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $locations = $this->locationRepository->findPublic();

        $q = $this->wordpressConnection->getDatabasePlatform()->getTruncateTableSQL('tld_location');
        $this->wordpressConnection->executeStatement($q);

        $pg = new ProgressBar($output, \count($locations));

        foreach ($locations as $location) {
            $pg->advance();

            $qb = $this->wordpressConnection->createQueryBuilder();

            try {
                $country = Countries::getName((string) $location->getAddress()->getCountry());
            } catch (MissingResourceException $missingResourceException) {
                $country = null;
            }

            $qb
                ->insert('tld_location')
                ->setValue('id', ':id')
                ->setValue('location', ':location')
                ->setValue('company_name', ':company_name')
                ->setValue('region', ':region')
                ->setValue('street1', ':street1')
                ->setValue('street2', ':street2')
                ->setValue('city', ':city')
                ->setValue('postal_code', ':postal_code')
                ->setValue('country', ':country')
                ->setValue('tel', ':tel')
                ->setValue('fax', ':fax')
                ->setValue('role', ':role')
                ->setParameters([
                    'id' => $location->getLegacyId(),
                    'location' => $location->getName(),
                    'company_name' => $location->getCompany(),
                    // todo #divisionproject I have no idea how to replace that and if it should be replaced
                    'region' => '',
                    'street1' => (string) $location->getAddress()->getStreet1(),
                    'street2' => (string) $location->getAddress()->getStreet2(),
                    'city' => (string) $location->getAddress()->getCity(),
                    'postal_code' => (string) $location->getAddress()->getPostalCode(),
                    'country' => (string) $country,
                    'tel' => (string) $location->getContact()->getTelephone(),
                    'fax' => (string) $location->getContact()->getFax(),
                    'role' => $location->getCapability()->isSso() ? 'SSO' : 'ERP',
                ])
            ;

            $this->wordpressConnection->executeQuery($qb->getSQL(), $qb->getParameters());
        }
        $pg->finish();

        return 0;
    }
}
