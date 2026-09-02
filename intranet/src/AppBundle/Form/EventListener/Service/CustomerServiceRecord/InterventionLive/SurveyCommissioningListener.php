<?php

declare(strict_types=1);

namespace AppBundle\Form\EventListener\Service\CustomerServiceRecord\InterventionLive;

use AppBundle\Enum\Service\CustomerServiceRecord\InterventionStatus;
use AppBundle\Factory\Service\SurveyCommissioningFactory;
use AppBundle\Form\Type\Service\CustomerServiceRecord\AnswerType;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class SurveyCommissioningListener implements EventSubscriberInterface
{
    public function __construct(
        private readonly SurveyCommissioningFactory $surveyCommissioningFactory,
        private readonly array $customerServiceRecord,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::PRE_SUBMIT => 'onPreSubmit',
        ];
    }

    public function onPreSetData(FormEvent $event): void
    {
        $data = $event->getData();

        if (isset($data['answerSurveyCustomerServiceRecords'])) {
            $event->getForm()->add('answerSurveyCustomerServiceRecords', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'label' => false,
                'entry_options' => [
                    'label' => 'false',
                ],
            ]);
        }
    }

    public function onPreSubmit(FormEvent $event): void
    {
        $data = $event->getData();

        if (InterventionStatus::SOLVED->value === $data['status'] && 'commissioning' === $this->customerServiceRecord['type']) {
            $answerList = $this->surveyCommissioningFactory->createAnswersCollection(
                $this->customerServiceRecord['answerSurveyCustomerServiceRecords']
            );
            $event->getForm()->add('answerSurveyCustomerServiceRecords', CollectionType::class, [
                'entry_type' => AnswerType::class,
                'label' => false,
                'entry_options' => [
                    'label' => 'false',
                ],
                'data' => $answerList,
            ]);
        }
    }
}
