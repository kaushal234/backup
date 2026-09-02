<?php

declare(strict_types=1);

namespace App\Command\Support;

use App\Repository\EquipmentRecordRepository;
use Doctrine\ORM\EntityManagerInterface;
use LegacyBundle\Manager\ModLogManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:fix-first-estimated-green-tag-date')]
class FixFirstEstimatedGreenTagDateCommand extends Command
{
    public function __construct(
        private readonly ModLogManager $modLogManager,
        private readonly EquipmentRecordRepository $equipmentRecordRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logs = $this->modLogManager->findFirstEstimatedGreenTagDateLogs();

        $output->writeln(\sprintf('<info>%d logs found</info>', \count($logs)));

        $count = 0;
        $countErrorDate = 0;
        $countNotFound = 0;
        $countAlreadyFilled = 0;

        foreach ($logs as $log) {
            $firstEstimatedGreenTagDate = $this->extractFirstEstimatedGreenTagDate($log['comment']);

            if (null === $firstEstimatedGreenTagDate) {
                ++$countErrorDate;
                $output->writeln(\sprintf('<comment>Unable to extract date from log #%s %s</comment>', $log['id'], $log['comment']));
                continue;
            }

            $equipmentRecord = $this->equipmentRecordRepository->findOneBy([
                'legacyId' => (int) $log['parent_id'],
            ]);

            if (null === $equipmentRecord) {
                ++$countNotFound;

                $output->writeln(\sprintf(
                    '<comment>EquipmentRecord legacyId %s not found</comment>',
                    $log['parent_id']
                ));

                continue;
            }

            if (null !== $equipmentRecord->getFirstEstimatedGreenTagDate()) {
                ++$countAlreadyFilled;
                continue;
            }

            $equipmentRecord->setFirstEstimatedGreenTagDate($firstEstimatedGreenTagDate);

            ++$count;

            // if you want see the comment and the date entered, for the importation we focus on error line
            // $output->writeln(sprintf('<fg=green>[%d]</> ER legacyId %s => %s comment: %s', $count, $log['parent_id'], $firstEstimatedGreenTagDate->format('Y-m-d H:i:s'), $log['comment']));

            if (0 === $count % 100) {
                $this->entityManager->flush();

                $output->writeln(\sprintf(
                    '<fg=yellow>Flushed %d records</>',
                    $count
                ));
            }
        }

        $this->entityManager->flush();

        $output->writeln(\sprintf('<info>%d date errors</info>', $countErrorDate));
        $output->writeln(\sprintf('<info>%d equipment records not found</info>', $countNotFound));
        $output->writeln(\sprintf('<info>%d already filled</info>', $countAlreadyFilled));
        $output->writeln(\sprintf('<fg=green;options=bold>%d first estimated green tag date updated</>', $count));

        return Command::SUCCESS;
    }

    private function extractFirstEstimatedGreenTagDate(string $comment): ?\DateTimeInterface
    {
        preg_match(
            "/<b>Estimated GT Date<\/b>\s*from\s*'0000-00-00'\s*to\s*'(?<date>\d{4}-\d{1,2}-\d{1,2})'/",
            $comment,
            $matches
        );

        if (!isset($matches['date'])) {
            preg_match(
                "/(?:=>|to)\s*'?(?<date>\d{4}-\d{1,2}-\d{1,2}(?: \d{2}:\d{2}:\d{2})?|\d{4}\/\d{1,2}\/\d{1,2}|\d{8})'?/",
                $comment,
                $matches
            );
        }

        if (!isset($matches['date'])) {
            return null;
        }

        $date = mb_trim(str_replace('/', '-', $matches['date']));

        if (preg_match('/^\d{8}$/', $date)) {
            $date = \sprintf('%s-%s-%s', mb_substr($date, 0, 4), mb_substr($date, 4, 2), mb_substr($date, 6, 2));
        }

        foreach (['!Y-m-d H:i:s', '!Y-n-j H:i:s', '!Y-m-d', '!Y-n-j'] as $format) {
            $dateTime = \DateTime::createFromFormat($format, $date);
            $errors = \DateTime::getLastErrors();

            if (
                false !== $dateTime
                && (false === $errors || (0 === $errors['warning_count'] && 0 === $errors['error_count']))
            ) {
                return $dateTime;
            }
        }

        return null;
    }
}
