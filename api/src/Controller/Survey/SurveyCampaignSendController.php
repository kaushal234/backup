<?php

declare(strict_types=1);

namespace App\Controller\Survey;

use App\Entity\Survey\Campaign;
use App\Manager\Survey\PublishedSurveyManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;

class SurveyCampaignSendController extends AbstractController
{
    private readonly PublishedSurveyManager $publishedSurveyManager;

    public function __construct(PublishedSurveyManager $publishedSurveyManager)
    {
        $this->publishedSurveyManager = $publishedSurveyManager;
    }

    public function __invoke(Campaign $campaign)
    {
        if (null !== $campaign->getSentAt()) {
            throw new GoneHttpException('This campaign has already been sent and this url should not be called anymore');
        }

        $this->publishedSurveyManager->sendCampaign($campaign);

        return new Response(null, Response::HTTP_NO_CONTENT);
    }
}
