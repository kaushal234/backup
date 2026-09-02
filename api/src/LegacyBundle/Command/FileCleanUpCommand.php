<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use Doctrine\DBAL\Connection;
use LegacyBundle\Command\Helper\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

#[AsCommand(name: 'legacy:file:cleanup')]
class FileCleanUpCommand extends Command
{
    public function __construct(
        private readonly ParameterBagInterface $parameters,
        private readonly Connection $legacyConnection,
        private readonly Filesystem $fileSystem,
        private readonly FileHelper $fileHelper,
    ) {
        parent::__construct();
        $this
            ->setDescription('Cleanup of files already migrated')
            ->addArgument('module', InputArgument::REQUIRED, 'Module to cleanup files from');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $module = $input->getArgument('module');
        if (null === $module) {
            $output->writeln('Module is a mandatory argument.');

            return Command::FAILURE;
        }

        $queryBuilder = $this->legacyConnection->createQueryBuilder();

        $queryBuilder
            ->select("mf.parent_id, module, f.poster, dt as created_at, description, REPLACE(REPLACE(REPLACE(REPLACE(f.filepath, '/var/www/intranet/current/uploads/mod_files/', ''), '/var/www/intranet/current/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/legacy/uploads/mod_files/', ''), '/var/www/alvest-web-portals/current/intranet/uploads/mod_files/', '') as filename, f.filename AS originalFilename, f.extension, f.mime")
            ->from('mod_files', 'mf')
            ->join('mf', 'file', 'f', 'mf.fid = f.id')
            ->where('module = :module')
            ->setParameter('module', $module)
        ;

        $result = $queryBuilder->executeQuery();
        $rowCount = $result->rowCount();

        if (0 === $rowCount) {
            $output->writeln('<info>Nothing to remove</info>');

            return Command::SUCCESS;
        }

        $progress = new ProgressBar($output);
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %remaining:10s% %memory:6s%');
        $progress->start($rowCount);
        $legacyUploadDir = $this->parameters->get('legacy.upload_dir');

        foreach ($result->fetchAllAssociative() as $data) {
            $filename = $data['filename'];
            $filepath = $legacyUploadDir."/mod_files/$filename";

            if (!file_exists($filepath)) {
                $output->writeln(\sprintf('<error>file %s does not exist</error>', $filepath));
                $progress->advance();
                continue;
            }

            if (null === $parsedFilepath = $this->fileHelper->parse($filepath, [], false)) {
                $output->writeln(\sprintf('<error>file %s skipped</error>', $filepath));
                $progress->advance();
                continue;
            }

            $this->fileSystem->remove($parsedFilepath);
            $output->writeln(\sprintf('File %s removed from storage', $filename));
            $progress->advance();
        }

        return Command::SUCCESS;
    }
}
