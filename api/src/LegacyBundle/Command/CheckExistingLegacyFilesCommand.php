<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use Doctrine\DBAL\Connection;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'legacy:file:check_exists')]
class CheckExistingLegacyFilesCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $parameters,
        private readonly Connection $legacyConnection,
    ) {
        parent::__construct();
        $this
            ->setDescription('Check existing legacy files on server')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $queryBuilder
            ->select("mf.parent_id, module, f.poster, dt as created_at, description, REPLACE(REPLACE(REPLACE(REPLACE(f.filepath, '/var/www/intranet/current/uploads/mod_files/', ''), '/var/www/intranet/current/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/uploads/mod_files/', '') as filename, f.filename AS originalFilename, f.extension, f.mime")
            ->from('service', 'er')
            ->leftJoin('er', 'mod_files', 'mf', 'mf.parent_id = er.id')
            ->join('mf', 'file', 'f', 'mf.fid = f.id')
            ->where('mf.module = :module')
            ->setParameter('module', 'ER')
        ;

        $result = $queryBuilder->executeQuery();
        $rowCount = $result->rowCount();

        $progress = new ProgressBar($output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
        $progress->start($rowCount);
        $legacyUploadDir = $this->parameters->get('legacy.upload_dir');

        foreach ($result->fetchAllAssociative() as $data) {
            $filename = $data['filename'];
            $filepath = $legacyUploadDir."/mod_files/$filename";

            if (!file_exists($filepath)) {
                $output->writeln(\sprintf('%s;%s;%s', $data['created_at'], $data['module'], $filepath));
                $progress->advance();
                continue;
            }

            $progress->advance();
        }

        return Command::SUCCESS;
    }
}
