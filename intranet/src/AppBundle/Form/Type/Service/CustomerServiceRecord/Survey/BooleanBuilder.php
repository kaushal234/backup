<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord\Survey;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Translation\TranslatableMessage;

class BooleanBuilder extends AbstractBuilder
{
    public function supports(array $data): bool
    {
        return 'boolean' === $data['questionSurveyCustomerServiceRecord']['type'];
    }

    public function reverse(array $value): array
    {
        $value['answer'] = $value['answer'] ? '1' : '0';

        return $value;
    }

    protected function getType(): string
    {
        return ChoiceType::class;
    }

    protected function getOptions(array $data): array
    {
        return [
            'label' => 'csr.questions.'.$data['questionSurveyCustomerServiceRecord']['name'],
            'label_attr' => ['class' => 'text-align:start'],
            'placeholder' => '',
            'choices' => [
                'YES' => true,
                'NO' => false,
            ],
            'required' => true,
        ];
    }

    protected function commentOptions(array $data): bool|array
    {
        return [
            'required' => false,
            'attr' => [
                'placeholder' => new TranslatableMessage('csr.questions.'.$data['questionSurveyCustomerServiceRecord']['name'].'_placeholder', [], 'customer_service_record'),
            ],
        ];
    }
}
