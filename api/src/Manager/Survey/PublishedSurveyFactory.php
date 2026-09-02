<?php

declare(strict_types=1);

namespace App\Manager\Survey;

use App\Entity\Directory\People;
use App\Entity\Sales\ExtranetUser;
use App\Entity\Survey\CustomerSurvey;
use App\Entity\Survey\PeopleSurvey;
use App\Entity\Survey\PublishedSurvey;
use App\Entity\SurveyTargetInterface;

class PublishedSurveyFactory
{
    public function createSurvey(SurveyTargetInterface $target): PublishedSurvey
    {
        switch (true) {
            case $target instanceof People:
                $publishedSurvey = new PeopleSurvey();
                break;
            case $target instanceof ExtranetUser:
                $publishedSurvey = new CustomerSurvey();
                break;
            default:
                throw new \InvalidArgumentException(\sprintf('Invalid argument supplied, no survey can be created from %s', $target::class));
        }

        $publishedSurvey->setTarget($target);

        return $publishedSurvey;
    }
}
