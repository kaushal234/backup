<?php

declare(strict_types=1);

namespace App\Tests\EventListener\Service;

use App\Entity\Activity\Comment;
use App\Entity\Service\CustomerServiceRecord\CommissioningCustomerServiceRecord;
use App\Entity\Service\SurveyCustomerServiceRecord\AnswerSurveyCustomerServiceRecord;
use App\Entity\Service\SurveyCustomerServiceRecord\QuestionSurveyCustomerServiceRecord;
use App\Event\Activity\CommentCreatedEvent;
use App\EventListener\Service\CustomerServiceRecordCommentListener;
use App\Notifier\Service\CustomerServiceRecord\CustomerServiceRecordCommissioningNotifier;
use PHPUnit\Framework\TestCase;

class CustomerServiceRecordCommentListenerTest extends TestCase
{
    public function testItDoesNothingWhenItemIsNotACommissioningCustomerServiceRecord(): void
    {
        $notifier = $this->createMock(CustomerServiceRecordCommissioningNotifier::class);
        $notifier->expects(self::never())->method('sendCommentOnImperfectSurveyMail');

        $listener = new CustomerServiceRecordCommentListener($notifier);
        $listener->onCommentPosted(new CommentCreatedEvent(new Comment(), new \stdClass(), true));
    }

    public function testItDoesNothingWhenNotMainRequest(): void
    {
        $notifier = $this->createMock(CustomerServiceRecordCommissioningNotifier::class);
        $notifier->expects(self::never())->method('sendCommentOnImperfectSurveyMail');

        $listener = new CustomerServiceRecordCommentListener($notifier);
        $listener->onCommentPosted(new CommentCreatedEvent(new Comment(), $this->createCustomerServiceRecord(['aspect' => '4', 'conformity' => '5', 'operational' => '5']), false));
    }

    public function testItNotifiesWhenSurveyIsFilledAndImperfect(): void
    {
        $comment = new Comment();
        $customerServiceRecord = $this->createCustomerServiceRecord(['aspect' => '5', 'conformity' => '4', 'operational' => '5']);

        $notifier = $this->createMock(CustomerServiceRecordCommissioningNotifier::class);
        $notifier->expects(self::once())
            ->method('sendCommentOnImperfectSurveyMail')
            ->with($customerServiceRecord, $comment);

        $listener = new CustomerServiceRecordCommentListener($notifier);
        $listener->onCommentPosted(new CommentCreatedEvent($comment, $customerServiceRecord, true));
    }

    public function testItDoesNotNotifyWhenSurveyIsPerfect(): void
    {
        $notifier = $this->createMock(CustomerServiceRecordCommissioningNotifier::class);
        $notifier->expects(self::never())->method('sendCommentOnImperfectSurveyMail');

        $listener = new CustomerServiceRecordCommentListener($notifier);
        $listener->onCommentPosted(new CommentCreatedEvent(
            new Comment(),
            $this->createCustomerServiceRecord(['aspect' => '5', 'conformity' => '5', 'operational' => '5']),
            true
        ));
    }

    public function testItDoesNotNotifyWhenSurveyIsPartiallyFilled(): void
    {
        $notifier = $this->createMock(CustomerServiceRecordCommissioningNotifier::class);
        $notifier->expects(self::never())->method('sendCommentOnImperfectSurveyMail');

        $listener = new CustomerServiceRecordCommentListener($notifier);
        $listener->onCommentPosted(new CommentCreatedEvent(
            new Comment(),
            $this->createCustomerServiceRecord(['aspect' => '4', 'conformity' => '5']),
            true
        ));
    }

    public function testItDoesNotNotifyWhenSurveyIsEmpty(): void
    {
        $notifier = $this->createMock(CustomerServiceRecordCommissioningNotifier::class);
        $notifier->expects(self::never())->method('sendCommentOnImperfectSurveyMail');

        $listener = new CustomerServiceRecordCommentListener($notifier);
        $listener->onCommentPosted(new CommentCreatedEvent(new Comment(), $this->createCustomerServiceRecord([]), true));
    }

    /**
     * @param array<string, string> $answers a map of question name to answer value
     */
    private function createCustomerServiceRecord(array $answers): CommissioningCustomerServiceRecord
    {
        $customerServiceRecord = new CommissioningCustomerServiceRecord();

        foreach ($answers as $questionName => $answerValue) {
            $question = new QuestionSurveyCustomerServiceRecord();
            $question->name = $questionName;

            $answer = new AnswerSurveyCustomerServiceRecord();
            $answer->questionSurveyCustomerServiceRecord = $question;
            $answer->setAnswer($answerValue);

            $customerServiceRecord->addAnswerSurveyCustomerServiceRecord($answer);
        }

        return $customerServiceRecord;
    }
}
