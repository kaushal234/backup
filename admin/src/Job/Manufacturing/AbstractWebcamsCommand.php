<?php

declare(strict_types=1);

namespace App\Job\Manufacturing;

use App\Job\LoggerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractWebcamsCommand extends LoggerAwareCommand
{
    abstract protected function getConfig(): array;

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        global $UPLOADS_PATH;

        $timezone = new \DateTimeZone('Europe/Paris');
        $localTime = (new \DateTime('now', $timezone))->format('His');
        if ($localTime < 115500 || $localTime >= 121000) {
            $this->logger->warning('Started script at a forbidden time');
        }

        $message = [];
        $conf = $this->getConfig();
        $pathDestination = $UPLOADS_PATH.'/webcam';

        foreach ($conf as $camConf) {
            $file = $pathDestination.'/'.$camConf['dest'];
            $errorMessage = "{$camConf['bu']} - {$camConf['cam']} -> ERROR";

            if (true !== $camConf['curl']) {
                $wasCopied = copy($camConf['url'], $file);

                if (false === $wasCopied) {
                    $message[] = $errorMessage;
                    $this->logger->error(\sprintf('Could not update %s', $file));
                    continue;
                }

                $this->logger->info(\sprintf('File %s successfully updated', $file));

                continue;
            }

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

    protected function makeCurlCall($url, $username, $password)
    {
        $options = [
            \CURLOPT_URL => $url,
            \CURLOPT_RETURNTRANSFER => true,
            \CURLOPT_USERPWD => $username.':'.$password,
            \CURLOPT_HTTPAUTH => \CURLAUTH_DIGEST,
        ];

        $ch = curl_init();
        curl_setopt_array($ch, $options);

        try {
            $rawResponse = curl_exec($ch);

            // validate HTTP status code (user/password credential issues)
            $statusCode = (int) curl_getinfo($ch, \CURLINFO_HTTP_CODE);

            if (200 !== $statusCode) {
                $this->logger->error("Status code was '$statusCode'");

                return false;
            }

            return $rawResponse;
        } catch (\Exception $ex) {
            $this->logger->error('Curl Exception: '.$ex->getMessage());

            return false;
        }
    }
}
