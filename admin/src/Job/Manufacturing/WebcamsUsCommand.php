<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'manufacturing:webcams:us')]
class WebcamsUsCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from WIN';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $UPLOADS_PATH;

        $cameras = [
            'http://admin:letmein@CAM_MIL_SHOP.tld-america.com/snapshot.cgi?user=admin&pwd=letmein' => 'WIN_SHOP_01.jpg',
            'http://admin:letmein@CAM_ACU_SHOP.tld-america.com/snapshot.cgi?user=admin&pwd=letmein' => 'WIN_SHOP_02.jpg',
            'http://admin:letmein@CAM_GPU_SHOP.tld-america.com/snapshot.cgi?user=admin&pwd=letmein' => 'WIN_SHOP_03.jpg',
            'http://admin:TldGpu1@192.111.3.149/dms.jpg' => 'WIN_SHOP_04.jpg',
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
