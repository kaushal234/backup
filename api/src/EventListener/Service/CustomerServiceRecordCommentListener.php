<?php

declare(strict_types=1);

namespace App\EventListener\Service;

use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\SurveyCustomerServiceRecord\QuestionSurveyCustomerServiceRecord;
use App\Event\Activity\CommentCreatedEvent;
use App\Notifier\Service\CustomerServiceRecord\CustomerServiceRecordCommissioningNotifier;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsEventListener(event: CommentCreatedEvent::class, method: 'onCommentPosted')]
readonly class CustomerServiceRecordCommentListener
{
    public function __construct(
        private CustomerServiceRecordCommissioningNotifier $notifier,
    ) {
    }

    public function onCommentPosted(CommentCreatedEvent $event): void
    {
        $customerServiceRecord = $event->getItem();

        if (!$customerServiceRecord instanceof CommissioningCustomerServiceRecord || !$event->isMainRequest()) {
            return;
        }

        if (!$this->isFilledImperfectSurvey($customerServiceRecord)) {
            return;
        }

        $this->notifier->sendCommentOnImperfectSurveyMail($customerServiceRecord, $event->getComment());
    }

    /**
     * The survey must be fully answered and at least one rating question must not have the maximum score.
     */
    private function isFilledImperfectSurvey(CommissioningCustomerServiceRecord $customerServiceRecord): bool
    {
        $imperfect = false;

        foreach (QuestionSurveyCustomerServiceRecord::RATING_QUESTIONS as $questionName) {
            $answer = $customerServiceRecord->getAnswerByQuestionName($questionName);

            if (null === $answer) {
                return false;
            }

            if ('5' !== $answer) {
                $imperfect = true;
            }
        }

        return $imperfect;
    }
}
