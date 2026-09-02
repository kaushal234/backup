<?php

declare(strict_types=1);

namespace App\Doctrine\EventListener;

use App\Entity\Survey\PublishedSurvey;
use Doctrine\ORM\Mapping as ORM;

class PublishedSurveyTokenListener
{
    #[ORM\PrePersist]
    public function setDefaultValues(PublishedSurvey $publishedSurvey)
    {
        $currentToken = $publishedSurvey->getToken();

        if (null === $currentToken) {
            $survey = $publishedSurvey->getCampaign()->getModel();
            $token = \sprintf('%d-%s.%s', $survey->getId(), date('dgmyHisu'), bin2hex(random_bytes(32)));

            $publishedSurvey->setToken($token);
        }
    }
}
