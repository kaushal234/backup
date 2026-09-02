<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'manufacturing:webcams:ca')]
class WebcamsCaCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from SHE';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $UPLOADS_PATH;

        $cameras = [
            'http://admin:letmein@10.0.0.29/dms.jpg' => 'SHE_SHOP_01.jpg',
            'http://admin:letmein@10.0.0.27/dms.jpg' => 'SHE_SHOP_02.jpg',
            'http://admin:letmein@10.0.0.28/dms.jpg' => 'SHE_SHOP_03.jpg',
        ];

        foreach ($cameras as $source => $destinationFile) {
            if (false === copy($source, "$UPLOADS_PATH/webcam/$destinationFile")) {
                $this->logger->error(\sprintf('Could not update %s', $destinationFile));
                continue;
            }
            $this->logger->info(\sprintf('File %s successfully updated', $destinationFile));
        }

        return Command::SUCCESS;
    }
}
