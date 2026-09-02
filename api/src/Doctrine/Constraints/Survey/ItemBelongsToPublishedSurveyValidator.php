<?php

declare(strict_types=1);

namespace App\Doctrine\Constraints\Survey;

use App\Entity\Survey\PublishedSurvey;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ItemBelongsToPublishedSurveyValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): bool
    {
        if (!$value->getPublishedSurvey() instanceof PublishedSurvey) {
            return true;
        }

        /** @var PublishedSurvey $survey */
        $survey = $value->getPublishedSurvey();

        $items = $survey->getCampaign()->getModel()->getItems();

        if (!$items->contains($value->getItem())) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ class_name }}', $value::class)
                ->setParameter('{{ survey_published_token }}', $value->getPublishedSurvey()->getToken())
                ->addViolation();

            return false;
        }

        return true;
    }
}
