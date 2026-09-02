<?php

declare(strict_types=1);

namespace App\Controller\Survey;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Entity\Survey\Survey;
use App\Manager\Survey\PublishedSurveyManager;
use App\Manager\Survey\SurveyPublicationModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class SurveyCampaignPublishController extends AbstractController
{
    private readonly SerializerInterface $serializer;
    private readonly ValidatorInterface $validator;
    private readonly PublishedSurveyManager $publishedSurveyManager;

    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator, PublishedSurveyManager $publishedSurveyManager)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->publishedSurveyManager = $publishedSurveyManager;
    }

    public function __invoke(Survey $survey, Request $request)
    {
        /** @var SurveyPublicationModel $model */
        $model = $this->serializer->deserialize($request->getContent(), SurveyPublicationModel::class, JsonEncoder::FORMAT);
        $model->setSurvey($survey);

        if ($survey->getItems()->isEmpty()) {
            throw new BadRequestHttpException('The survey must have at least one item');
        }

        $violations = $this->validator->validate($model);

        if (\count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $this->publishedSurveyManager->createCampaign($model);
    }
}
