<?php

declare(strict_types=1);

namespace App\AI\Service\Summarizer;

use App\AI\Platform\Invoker\PlatformInvokerInterface;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

class AILogSummarizer
{
    public function __construct(
        private readonly PlatformInvokerInterface $invoker,
    ) {
    }

    public function summarizeRequest(array $messages): string
    {
        $systemPrompt = <<<'PROMPT'
                You a generating a title for a conversation.
                Rules : 
                    - 3 to 6 words
                    - No quotes
                    - No final point
                    - Clear and concrete style
                    - Put an emoji at first only if it helps the understanding
            PROMPT;

        $parts = [];
        if ([] !== $messages) {
            $parts[] = "Messages of the conversation:\n".implode("\n\n", $messages);
        }

        return $this->summarize($systemPrompt, $parts);
    }

    public function summarizeConversation(array $messages, ?string $existingSummary = null): string
    {
        $systemPrompt = <<<'PROMPT'
            You are summarizing a chat conversation.

            Your task is to produce an updated summary of the conversation using:
            - the previous summary, if provided
            - the new conversation messages

            Rules:
            - Keep only useful context for continuing the conversation
            - Preserve important facts, decisions, constraints, user preferences, and pending actions
            - Do not invent information
            - Stay concise
            - Write the summary in plain text
            - Summary should not exceed 1200 words
            PROMPT;

        $parts = [];
        if (null !== $existingSummary && '' !== mb_trim($existingSummary)) {
            $parts[] = \sprintf("Previous summary:\n%s", mb_trim($existingSummary));
        }

        if ([] !== $messages) {
            $parts[] = "New messages to integrate:\n".implode("\n\n", $messages);
        }

        $parts[] = 'Please produce an updated conversation summary.';

        return $this->summarize($systemPrompt, $parts);
    }

    private function summarize(string $systemPrompt, array $parts): string
    {
        $input = new MessageBag();
        $input->add(Message::forSystem($systemPrompt));
        $input->add(Message::ofUser(implode("\n\n", $parts)));

        return mb_trim($this->invoker->invokeAsText('mistral-small-latest', $input));
    }
}
