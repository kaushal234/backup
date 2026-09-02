<?php

declare(strict_types=1);

namespace App\Command\TLDGroupSync;

use App\Entity\HumanResources\Job;
use App\Repository\HumanResources\JobRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'tld:group:sync:jobs')]
class TLDGroupJobsSyncCommand extends Command
{
    private readonly JobRepository $jobRepository;
    private readonly Connection $wordpressConnection;

    public function __construct(JobRepository $jobRepository, Connection $wordpressConnection)
    {
        parent::__construct();
        $this->setDescription('Sync Jobs to TLD Group Wordpress database');

        $this->jobRepository = $jobRepository;
        $this->wordpressConnection = $wordpressConnection;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Collection|Job[] $jobs */
        $jobs = $this->jobRepository->findPublic();

        $q = $this->wordpressConnection->getDatabasePlatform()->getTruncateTableSQL('tld_job');
        $this->wordpressConnection->executeStatement($q);

        foreach ($jobs as $job) {
            $qb = $this->wordpressConnection->createQueryBuilder();

            $businessUnit = $job->getBusinessUnit();
            $country = null;

            switch ($job->getBusinessUnit()->getName()) {
                case 'TLD LEB':
                    $country = 'Belgique';
                    $city = 'Frameries';
                    $state = null;
                    break;
                case 'TLD SHE':
                    $country = 'Canada';
                    $city = 'Sherbrooke';
                    $state = 'QC';
                    break;
                case 'TLD WUX':
                    $country = 'China';
                    $city = 'Wuxi';
                    $state = null;
                    break;
                case 'TLD WIN':
                case 'TLD WIM':
                    $country = 'USA';
                    $city = 'Windsor';
                    $state = 'CT';
                    break;
                case 'TLD SHA':
                    $country = 'China';
                    $city = 'Shanghai';
                    $state = null;
                    break;
                case 'TLD MTL':
                case 'TLD EUR':
                case 'TLD DTV':
                    $country = 'France';
                    $city = 'Sorigny';
                    $state = null;
                    break;
                case 'TLD STL':
                    $country = 'France';
                    $city = 'Saint-Lin';
                    $state = null;
                    break;
                case 'ALVEST EQUIPMENT SERVICES':
                    $country = 'France';
                    $city = 'Montlouis-sur-Loire';
                    $state = null;
                    break;
                case 'ALVEST':
                case 'ALVEST OEM':
                case 'TLD EMEAI':
                    $country = 'France';
                    $city = null;
                    $state = null;
                    break;
                case 'TLD SIN':
                    $country = 'Singapore';
                    $city = null;
                    $state = null;
                    break;
                default:
                    $output->writeln(
                        \sprintf('Job "%s" has been skipped because BU "%s" could not be mapped to a Wordpress Country',
                            $job->getTitle(),
                            $businessUnit->getName()
                        ));
                    continue 2;
            }

            $qb
                ->insert('tld_job')
                ->setValue('id', ':id')
                ->setValue('dt', ':dt')
                ->setValue('title', ':title')
                ->setValue('diploma', ':diploma')
                ->setValue('experience', ':experience')
                ->setValue('description', ':description')
                ->setValue('country', ':country')
                ->setValue('city', ':city')
                ->setValue('state', ':state')
                ->setParameters([
                    'id' => $job->getId(),
                    'dt' => $job->getCreatedAt()->format('Y-m-d'),
                    'title' => $job->getTitle(),
                    'diploma' => $job->getDiploma(),
                    'experience' => $job->getExperience(),
                    'description' => $job->getDescription(),
                    'country' => $country,
                    'city' => $city,
                    'state' => $state,
                ])
            ;

            $this->wordpressConnection->executeQuery($qb->getSQL(), $qb->getParameters());
        }

        return 0;
    }
}
