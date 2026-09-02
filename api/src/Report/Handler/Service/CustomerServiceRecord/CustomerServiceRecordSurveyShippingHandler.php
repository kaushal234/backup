<?php

declare(strict_types=1);

namespace App\Report\Handler\Service\CustomerServiceRecord;

use App\Entity\Service\SurveyCustomerServiceRecord\AnswerSurveyCustomerServiceRecord;
use App\Report\DataProvider\Extractor\LabelExtractor;
use App\Report\DataProvider\ReportDataProvider;
use App\Report\Handler\DefaultPriorityTrait;
use App\Report\Handler\IrisExtractorBuilderFactoryAwareTrait;
use App\Report\Handler\IsGrantedTrait;
use App\Report\Handler\ReportHandlerInterface;
use App\Report\Handler\ReportQueriesBuilderFactoryAwareTrait;
use Doctrine\DBAL\Connection;

class CustomerServiceRecordSurveyShippingHandler implements ReportHandlerInterface
{
    use DefaultPriorityTrait;
    use IrisExtractorBuilderFactoryAwareTrait;
    use IsGrantedTrait;
    use ReportQueriesBuilderFactoryAwareTrait;

    private readonly Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function handle(string $resourceClass, string $x, string $y, array $options = []): ?ReportDataProvider
    {
        if (AnswerSurveyCustomerServiceRecord::class !== $resourceClass || 'answer' !== $x) {
            return null;
        }

        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder
            ->select('count(a.answer) as value')
            ->addSelect('a.answer as x')
            ->addSelect('"Value" as y')
            ->from('answer_survey_customer_service_record', 'a')
            ->leftJoin('a', 'question_survey_customer_service_record', 'question', 'a.question_survey_customer_service_record_id = question.id')
            ->where('question.name = :shipping')
            ->groupBy('x')
            ->setParameter('shipping', 'shipping')
        ;

        $results = $queryBuilder->executeQuery()->fetchAllAssociative();

        return new ReportDataProvider(
            $results,
            (new LabelExtractor($results, 'x'))(),
            (new LabelExtractor($results, 'y'))()
        );
    }
}
