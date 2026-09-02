<?php

declare(strict_types=1);

namespace App\Controller\Survey;

use App\Entity\Survey\PublishedSurvey;
use App\Manager\Survey\PublishedSurveyManager;
use App\Repository\Survey\PublishedSurveyRepository;
use App\Survey\SurveyItemsParser;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SurveyGetPublishSurveyController extends AbstractController
{
    private readonly PublishedSurveyRepository $surveyRepository;
    private readonly SurveyItemsParser $surveyItemsParser;
    private readonly PublishedSurveyManager $publishedSurveyManager;

    public function __construct(
        PublishedSurveyRepository $surveyRepository,
        SurveyItemsParser $surveyItemsParser,
        PublishedSurveyManager $publishedSurveyManager
    ) {
        $this->surveyRepository = $surveyRepository;
        $this->surveyItemsParser = $surveyItemsParser;
        $this->publishedSurveyManager = $publishedSurveyManager;
    }

    public function __invoke($token)
    {
        /** @var PublishedSurvey $survey */
        $survey = $this->surveyRepository->findOneBy(['token' => $token]);

        $this->surveyItemsParser->parseAvailableItems($survey);

        if ($this->publishedSurveyManager->isExpired($survey)) {
            throw new HttpException(Response::HTTP_GONE, \sprintf('Survey "%s"  is expired.', $survey->getCampaign()->getModel()->getName()));
        }

        if ($this->publishedSurveyManager->isClosed($survey)) {
            throw new HttpException(Response::HTTP_LOCKED, \sprintf('Survey "%s"  is closed.', $survey->getCampaign()->getModel()->getName()));
        }

        return $survey;
    }
}
