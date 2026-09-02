<?php

declare(strict_types=1);

namespace AppBundle\Factory\Service;

use ApiBundle\Client;

class SurveyCommissioningFactory
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    public function createAnswersCollection(array $knewAnswers)
    {
        $questions = $this->client->get('/service/question_survey_customer_service_records')['hydra:member'];
        $knewAnswersMap = $this->mapKnewAnswersById($knewAnswers);

        $missingAnswers = array_map(static fn ($question) => [
            'questionSurveyCustomerServiceRecord' => $question,
            'answer' => null,
        ], array_filter($questions, static fn ($question) => !isset($knewAnswersMap[$question['@id']])));

        $fullAnswers = array_merge($knewAnswers, $missingAnswers);
        usort($fullAnswers, static fn ($first, $second) => $first['questionSurveyCustomerServiceRecord']['@id'] <=> $second['questionSurveyCustomerServiceRecord']['@id']);

        return $fullAnswers;
    }

    public function mapKnewAnswersById(array $knewAnswers): array
    {
        $map = [];
        foreach ($knewAnswers as $answer) {
            if (isset($answer['questionSurveyCustomerServiceRecord']['@id'])) {
                $map[$answer['questionSurveyCustomerServiceRecord']['@id']] = $answer;
            }
        }

        return $map;
    }
}
