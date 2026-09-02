<?php

declare(strict_types=1);

namespace LegacyBundle\Command\Service;

use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'legacy:answers_customer_service_record:fix_double_write',
    description: 'Remove duplication of answer on legacy'
)]
class FixCustomerServiceRecordDoubleWriteCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly Connection $legacyConnection,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $logger = new ConsoleLogger($output);
        $commissioningCustomerServiceRecords = $this->entityManager->getRepository(CommissioningCustomerServiceRecord::class)->findAll();

        $progressBar = new ProgressBar($output, \count($commissioningCustomerServiceRecords));
        foreach ($commissioningCustomerServiceRecords as $customerServiceRecord) {
            $progressBar->advance();
            if (!$customerServiceRecord->getAnswerSurveyCustomerServiceRecords()->count()) {
                continue;
            }

            // Select answer on legacy for a specific CSR
            $sql = <<<'SQL'
                SELECT *
                FROM mod_kpi
                WHERE parent_id = :customerServiceRecordLegacyId
                SQL;

            $modKpiAnswers = $this->legacyConnection->executeQuery($sql, [
                'customerServiceRecordLegacyId' => $customerServiceRecord->getLegacyId(),
            ])->fetchAllAssociative();

            // Get all unique questions concerned by this CSR
            $answerHeaders = array_unique(array_column($modKpiAnswers, 'key1'));

            foreach ($answerHeaders as $header) {
                // Get answers for a question
                $answersForHeader = array_filter($modKpiAnswers, static function ($value) use ($header) {
                    return $value['key1'] === $header;
                });

                if (1 === \count($answersForHeader)) {
                    continue;
                }

                // Search the API answer to get legacy ID of mod_kpi
                $criteria = Criteria::create()->where(Criteria::expr()->eq('questionSurveyCustomerServiceRecord.name', $header));
                $apiAnswer = $customerServiceRecord->getAnswerSurveyCustomerServiceRecords()->matching($criteria);

                if (1 !== $apiAnswer->count()) {
                    $logger->error(\sprintf('Hum more than one answer on API ????? #%d', $customerServiceRecord->getLegacyId()));
                    continue;
                }

                $apiAnswer = $apiAnswer->first();
                foreach ($answersForHeader as $answerForHeader) {
                    // No action for legacy answer linked to API answer
                    if ($answerForHeader['id'] === $apiAnswer->getLegacyId()) {
                        continue;
                    }

                    // Remove the answer not linked to API
                    $deleteSql = <<<'SQL'
                        DELETE FROM mod_kpi
                        WHERE id = :id
                        SQL;

                    $this->legacyConnection->executeQuery($deleteSql, ['id' => $answerForHeader['id']]);
                }
            }
        }

        // Set name to commissioning, for CSR answers
        $sql = <<<'SQL'
            UPDATE mod_kpi
            SET name = 'commissioning'
            WHERE module = 'CSR' AND key1 != 'solIncomplete'
            SQL;

        $this->legacyConnection->executeQuery($sql);

        $progressBar->finish();

        return Command::SUCCESS;
    }
}
