<?php

declare(strict_types=1);

namespace App\MessageHandler\Sales;

use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\Directory\People;
use App\Entity\Sales\MarketIntelligence\MarketIntelligence;
use App\Message\Sales\NotifyMarketIntelligenceComment;
use App\Notifier\Sales\MarketIntelligence\MarketIntelligenceNotifier;
use App\Notifier\Sales\MarketIntelligence\RecipientsFinder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsMessageHandler]
final class MarketIntelligenceEmailCommentHandler
{
    private readonly IriConverterInterface $iriConverter;
    private readonly MarketIntelligenceNotifier $notifier;
    private readonly RecipientsFinder $recipientsFinder;
    private readonly ValidatorInterface $validator;

    public function __construct(IriConverterInterface $iriConverter, MarketIntelligenceNotifier $notifier, RecipientsFinder $recipientsFinder, ValidatorInterface $validator)
    {
        $this->iriConverter = $iriConverter;
        $this->notifier = $notifier;
        $this->recipientsFinder = $recipientsFinder;
        $this->validator = $validator;
    }

    public function __invoke(NotifyMarketIntelligenceComment $message)
    {
        /** @var MarketIntelligence $marketIntelligence */
        $marketIntelligence = $this->iriConverter->getResourceFromIri($message->getResourceIri());

        /** @var People $user */
        $user = $this->iriConverter->getResourceFromIri($message->getUserIri());

        $constraint = new Email();
        $constraint->mode = 'strict';

        /** @var People $recipient */
        foreach ($this->recipientsFinder->findRecipients($marketIntelligence) as $recipient) {
            $violations = $this->validator->validate($recipient->getEmail(), $constraint);
            if (0 < $violations->count()) {
                continue;
            }

            $this->notifier->sendCommentEmail($marketIntelligence, $user, $recipient->getEmail(), $message->getComment());
        }
    }
}
