<?php

declare(strict_types=1);

namespace App\Job\Website;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

include_once 'dms.inc.php';

#[AsCommand(name: 'website:sync:data')]
class TldGroupSyncCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Synchronize files with TLD group wordpress website';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $TLD_GROUP_PATH;

        /**
         * Sync TLD media files
         * ---------------------
         * -> mv file directly from DMS to wordpress upload folder.
         */
        $mediaBaseFilePath = "$TLD_GROUP_PATH/wp-content/uploads/tld-media/";
        $mediaList = [
            // Core values
            [
                'ref' => 1438,
                'file' => 'CoreValues-en.pdf',
            ],
            [
                'ref' => 1439,
                'file' => 'CoreValues-fr.pdf',
            ],
            [
                'ref' => 1440,
                'file' => 'CoreValues-zh.pdf',
            ],
            // Code of Ethics
            [
                'ref' => 236,
                'file' => 'CodeOfEthics-en.pdf',
            ],
            [
                'ref' => 238,
                'file' => 'CodeOfEthics-fr.pdf',
            ],
            [
                'ref' => 833,
                'file' => 'CodeOfEthics-zh.pdf',
            ],
            [
                'ref' => 860,
                'file' => 'general-terms-and-conditions-en.pdf',
            ],
            [
                'ref' => 861,
                'file' => 'general-terms-and-conditions-fr.pdf',
            ],
            [
                'ref' => 358,
                'file' => 'tld-general-warranty-conditions.pdf',
            ],
        ];

        // Begin to sync
        foreach ($mediaList as $media) {
            $fileDescription = "{DMS}#{$media['ref']}";
            try {
                // get DMS
                $dms = new \tldDMS((int) $media['ref']);
                if ($dms->isEmpty()) {
                    throw new \Exception("$fileDescription not found");
                }
                // get Active file
                $file = $dms->getActiveRevisionFile();
                // Copy to destination
                $destination = $mediaBaseFilePath.$media['file'];
                $copyError = copy($file->getFilepath(), $destination);
                if (!$copyError) {
                    throw new \Exception("Could not copy {$file->getFilepath()} to $destination");
                }
            } catch (\Exception $e) {
                $this->handleError("$fileDescription active revision file error: ".$e->getMessage());
            }
        }

        $this->logger->info('DMS documents inserted in wordpress database');

        /**
         * EXECUTE the wp plugin from contractor
         * import script for TLD data & img
         * result email to jpd + jl + contractor (conf in wp-admin).
         */
        $cmd = "php $TLD_GROUP_PATH/wp-content/themes/tld-group.com/inc/import/import.php";
        $errors = [];
        exec($cmd, $errors);

        foreach ($errors as $error) {
            $this->logger->warning($error);
        }

        $this->logger->info('Wordpress script executed successfully');

        return Command::SUCCESS;
    }

    private function handleError($err, $sql = '')
    {
        $this->logger->error($err);
        if (!empty($sql)) {
            $sqlMsg = "<br>Query -> $sql";
        }

        return \tldUtils::emailAttachment(
            'devteam@tld-america.com',
            'noreply@tld-gse.com',
            '[www.tld-group.com] Daily data synchronization error',
            "Please check ASAP<br>Error -> $err.$sqlMsg"
        );
    }
}
