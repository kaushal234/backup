<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\Directory\People;
use App\Entity\Service\TechnicianOnCall;
use App\Entity\Service\TechnicianOnCallMainFile;
use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\EntityCacheHelperFactory;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:technician_on_call:main_file')]
class ImportTechnicianOnCallMainFileCommand extends Command
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly FileHelper $fileHelper,
        private readonly EntityCacheHelperFactory $cacheFactory,
    ) {
        parent::__construct();
        $this->setDescription('Import TOC main file from legacy');
    }

    protected function configure(): void
    {
        $this
            ->addOption('tocIds', 'tocIds', InputOption::VALUE_REQUIRED, 'List of TOC IDs to import, separate by a comma')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $tocIdList = $input->getOption('tocIds');

        if (!$tocIdList) {
            $output->writeln('<error>No TOC ID provided</error>');

            return Command::FAILURE;
        }

        if (!preg_match('/^\d+(,\d+)*$/', $tocIdList)) {
            throw new \InvalidArgumentException('--tocIds doit contenir uniquement des nombres séparés par des virgules.');
        }

        $queryBuilder = $this->legacyConnection->createQueryBuilder();
        $queryBuilder
            ->select("
                mf.parent_id,
                 module,
                  f.poster,
                   dt as created_at,
                    description,
                     REPLACE(REPLACE(REPLACE(REPLACE(
                        f.filepath,
                         '/var/www/intranet/current/uploads/mod_files/', ''
                         ), '/var/www/intranet/current/legacy/uploads/mod_files/', ''
                         ), '/var/www/alvest-web-portals/current/intranet/legacy/uploads/mod_files/', ''
                         ), '/var/www/alvest-web-portals/current/intranet/uploads/mod_files/', ''
                         ) as filename,
                          f.filename AS originalFilename,
                           f.extension, f.mime"
            )
            ->from('mod_files', 'mf')
            ->join('mf', 'file', 'f', 'mf.fid = f.id')
            ->where('module = :module')
            ->andWhere('mf.parent_id in (:ids)')
            ->andWhere('mf.level IN (1, 3)')
            ->setParameter('module', 'TOC')
            ->setParameter('ids', $tocIdList)
        ;

        $tocFiles = $queryBuilder->executeQuery();

        $peopleCache = $this->cacheFactory->createEntityCache(People::class, 'email');

        $this->fileHelper->import(
            $tocFiles,
            $output,
            TechnicianOnCall::class,
            TechnicianOnCallMainFile::class,
            'filename',
            'parent_id',
            'mod_files',
            static function ($data) use ($peopleCache) {
                $metadata = [];
                if ($data['description']) {
                    $metadata['description'] = $data['description'];
                }

                if (null !== $poster = $peopleCache->fetch($data['poster'])) {
                    $metadata['poster'] = $poster;
                }

                if ($data['created_at']) {
                    $metadata['created_at'] = new \DateTime($data['created_at']);
                }

                return $metadata;
            }
        );

        return Command::SUCCESS;
    }
}
