<?php

declare(strict_types=1);

namespace LegacyBundle\Command;

use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\SurveyCustomerServiceRecord\AnswerSurveyCustomerServiceRecord;
use App\Entity\Service\SurveyCustomerServiceRecord\QuestionSurveyCustomerServiceRecord;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'legacy:import:service:survey')]
class ImportCustomerServiceRecordsSurveyCommand extends Command
{
    public function __construct(
        private readonly Connection $legacyConnection,
        private readonly EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
        $this->setDescription('Imports Survey For Commissioning Customer Service Records from legacy');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $commissioningCustomerServiceRecords = $this->entityManager->getRepository(CommissioningCustomerServiceRecord::class)->findAll();
        $questionSurveyCustomerServiceRecords = $this->entityManager->getRepository(QuestionSurveyCustomerServiceRecord::class)->findAll();
        $progressBar = new ProgressBar($output, \count($commissioningCustomerServiceRecords));

        foreach ($commissioningCustomerServiceRecords as $commissioningCustomerServiceRecord) {
            $progressBar->advance();

            $sql = <<<SQL
                SELECT id, parent_id, key1, val, comments
                FROM mod_kpi
                WHERE module = 'CSR'
                AND parent_id = {$commissioningCustomerServiceRecord->getLegacyId()}
                SQL;

            $surveys = $this->legacyConnection->executeQuery($sql)->fetchAllAssociative();
            if (empty($surveys)) {
                continue;
            }

            if (\count($surveys) > 3) {
                $surveys = \array_slice($surveys, 0, 3);
            }

            foreach ($surveys as $survey) {
                if (!\in_array($survey['key1'], ['aspect', 'conformity', 'operational'], true)) {
                    continue;
                }

                $question = current(array_filter($questionSurveyCustomerServiceRecords, static function ($question) use ($survey) {
                    return $question->name === $survey['key1'];
                }));

                if (!$question) {
                    continue;
                }

                $answer = $this->createAnswerSurveyCustomerServiceRecord($commissioningCustomerServiceRecord, $survey, $question);
                $commissioningCustomerServiceRecord->addAnswerSurveyCustomerServiceRecord($answer);
            }

            $this->entityManager->persist($commissioningCustomerServiceRecord);
        }
        $progressBar->finish();
        $this->entityManager->flush();

        return Command::SUCCESS;
    }

    private function createAnswerSurveyCustomerServiceRecord(
        CommissioningCustomerServiceRecord $commissioningCustomerServiceRecord,
        array $survey,
        QuestionSurveyCustomerServiceRecord $question
    ): AnswerSurveyCustomerServiceRecord {
        $answer = new AnswerSurveyCustomerServiceRecord();
        $answer->commissioningCustomerServiceRecord = $commissioningCustomerServiceRecord;
        $answer->setAnswer($survey['val'] ? (string) $survey['val'] : null);
        $answer->comment = $survey['comments'] ?? null;
        $answer->questionSurveyCustomerServiceRecord = $question;
        $answer->createdAt = $commissioningCustomerServiceRecord->completedAt ?? new \DateTime();
        $answer->setLegacyId($survey['id']);

        return $answer;
    }
}
