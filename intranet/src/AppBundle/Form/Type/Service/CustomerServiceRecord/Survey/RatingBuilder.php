<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord\Survey;

use AppBundle\Form\Type\Common\SurveyRatingType;

class RatingBuilder extends AbstractBuilder
{
    public function supports(array $data): bool
    {
        return 'rating' === $data['questionSurveyCustomerServiceRecord']['type'];
    }

    protected function getType(): string
    {
        return SurveyRatingType::class;
    }

    protected function getOptions(array $data): array
    {
        return [
            'label' => 'csr.questions.'.$data['questionSurveyCustomerServiceRecord']['name'],
            'label_attr' => ['class' => 'text-start'],
        ];
    }

    protected function commentOptions(array $data): bool|array
    {
        return true;
    }
}
