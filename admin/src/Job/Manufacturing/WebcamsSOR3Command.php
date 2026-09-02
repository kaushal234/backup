<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'manufacturing:webcams:sor3')]
class WebcamsSOR3Command extends AbstractWebcamsCommand
{
    public function getDescription(): string
    {
        return 'Get webcams pictures from sor3';
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $UPLOADS_PATH;

        $localTime = new \DateTime('now', new \DateTimeZone('Europe/Paris'));
        $start = (clone $localTime)->setTime(11, 55, 0);
        $end = (clone $localTime)->setTime(18, 00, 0);
        if ($localTime < $start || $localTime >= $end) {
            $this->logger->warning('Started script at a forbidden time');
        }

        $message = [];
        $conf = $this->getConfig();
        $pathDestination = $UPLOADS_PATH.'/webcam/timelapse_sor3';
        $dateStamp = $localTime->format('Ymd');   // e.g. 20250814
        $timeStamp = $localTime->format('His');   // e.g. 115732

        foreach ($conf as $camConf) {
            $file = \sprintf(
                '%s/%s_%s_%s.jpg',
                $pathDestination,
                $camConf['dest'],
                $dateStamp,
                $timeStamp
            );
            $errorMessage = "{$camConf['bu']} - {$camConf['cam']} -> ERROR";

            $resp = $this->makeCurlCall($camConf['url'], $camConf['username'], $camConf['password']);
            if (false === $resp) {
                $message[] = $errorMessage;

                $this->logger->error(\sprintf('Could not update %s', $file));
                continue;
            }

            file_put_contents($file, $resp);

            $this->logger->info(\sprintf('File %s successfully updated', $file));
        }

        if (\count($message)) {
            \tldUtils::emailAttachment(
                'networkadmin@tld-europe.com',
                'noreply@tld-gse.com',
                'WEB CAM script results',
                implode('<br>', $message)
            );
        }

        return Command::SUCCESS;
    }

    protected function getConfig(): array
    {
        return [
            [
                'bu' => 'SOR',
                'cam' => 'CAM 5',
                'url' => 'http://CMA000025.tld-europe.local/jpg/1/image.jpg?Axis-Orig-Sw=true',
                'dest' => 'SOR_05',
                'curl' => true,
                'username' => 'root',
                'password' => ']9Y6xD\+<{[#txu{E9',
            ],
        ];
    }
}
