<?php

declare(strict_types=1);

namespace App\Controller\Survey;

use App\Entity\Survey\Answer;
use App\Entity\Survey\PublishedSurvey;
use App\Manager\Survey\PublishedSurveyManager;
use App\Repository\Survey\PublishedSurveyRepository;
use App\Survey\SurveyItemsParser;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SurveysPostAnswerController extends AbstractController
{
    public function __construct(
        private readonly PublishedSurveyRepository $surveyRepository,
        private readonly SurveyItemsParser $surveyItemsParser,
        private readonly PublishedSurveyManager $publishedSurveyManager,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke($token, Answer $data)
    {
        $survey = $this->surveyRepository->findOneBy(['token' => $token]);

        if (!$survey instanceof PublishedSurvey) {
            throw $this->createNotFoundException('Survey Not Found');
        }

        $data->setPublishedSurvey($survey);

        $this->surveyItemsParser->parseAvailableItems($survey);

        if ($this->publishedSurveyManager->isExpired($survey)) {
            throw new HttpException(Response::HTTP_GONE, \sprintf('Survey "%s" is expired.', $survey->getCampaign()->getModel()->getName()));
        }

        if ($this->publishedSurveyManager->isCompleted($survey)) {
            throw new HttpException(Response::HTTP_NO_CONTENT);
        }

        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }
}
