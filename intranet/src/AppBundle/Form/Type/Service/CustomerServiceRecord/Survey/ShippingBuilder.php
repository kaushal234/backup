<?php

declare(strict_types=1);

namespace AppBundle\Form\Type\Service\CustomerServiceRecord\Survey;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShippingBuilder extends AbstractBuilder
{
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    public function supports(array $data): bool
    {
        if ('choice' !== $data['questionSurveyCustomerServiceRecord']['type']) {
            return false;
        }

        return 'shipping' === $data['questionSurveyCustomerServiceRecord']['name'];
    }

    public function transform(array $value): array
    {
        $value['answer'] = explode(',', $value['answer']);

        return $value;
    }

    public function reverse(array $value): array
    {
        $value['answer'] = implode(',', $value['answer']);

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
            'choices' => [
                $this->translator->trans('csr.questions.shipping_choices.no_damages', [], 'customer_service_record') => 'no_damages',
                $this->translator->trans('csr.questions.shipping_choices.transport', [], 'customer_service_record') => 'transport',
                $this->translator->trans('csr.questions.shipping_choices.factory', [], 'customer_service_record') => 'factory',
            ],
            'required' => true,
            'multiple' => true,
            'expanded' => true,
            'constraints' => [
                new NotBlank(),
                new Callback(static function (array $data, ExecutionContextInterface $context) {
                    if (\in_array('no_damages', $data, true) && \count($data) > 1) {
                        $context
                            ->buildViolation('No shipping damages : cannot be selected with other values')
                            ->addViolation()
                        ;
                    }
                }),
            ],
        ];
    }

    protected function commentOptions(array $data): bool|array
    {
        return true;
    }
}
