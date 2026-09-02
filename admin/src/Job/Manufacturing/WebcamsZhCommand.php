<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'manufacturing:webcams:zh')]
class WebcamsZhCommand extends LoggerAwareCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from China';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $UPLOADS_PATH;

        $cameras = [
            'http://10.12.50.4/jpg/1/image.jpg?Axis-Orig-Sw=true' => 'SHA_SHOP_05.jpg',
            'http://10.12.50.3/jpg/1/image.jpg?Axis-Orig-Sw=true' => 'SHA_SHOP_06.jpg',
            'http://10.12.50.2/jpg/1/image.jpg?Axis-Orig-Sw=true' => 'SHA_SHOP_07.jpg',
            'http://www:tired@10.12.82.61/jpg/1/image.jpg' => 'WUX_SHOP_01.jpg',
            'http://10.12.82.60/jpg/1/image.jpg' => 'WUX_SHOP_10.jpg',
            'http://www:tired@10.12.82.62/jpg/1/image.jpg' => 'WUX_SHOP_04.jpg',
            'http://www:tired@10.12.82.63/jpg/1/image.jpg' => 'WUX_SHOP_05.jpg',
            'http://www:tired@10.12.82.64/jpg/1/image.jpg' => 'WUX_SHOP_06.jpg',
            'http://www:tired@10.12.82.65/jpg/1/image.jpg' => 'WUX_SHOP_07.jpg',
            'http://www:tired@10.12.82.66/jpg/1/image.jpg' => 'WUX_SHOP_08.jpg',
            'http://www:tired@10.12.82.67/jpg/1/image.jpg' => 'WUX_SHOP_09.jpg',
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
