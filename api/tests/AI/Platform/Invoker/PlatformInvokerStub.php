<?php

declare(strict_types=1);

namespace App\Tests\AI\Platform\Invoker;

use App\AI\Platform\Invoker\PlatformInvokerInterface;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\MessageBag;
use Symfony\AI\Platform\Message\UserMessage;

class PlatformInvokerStub implements PlatformInvokerInterface
{
    public function invokeAsText(string $model, MessageBag $messageBag): string
    {
        $prompt = $this->extractPromptText($messageBag);

        if (str_contains($prompt, 'contract analysis assistant')) {
            return $this->fakeContractExtraction();
        }

        return 'Fake Response';
    }

    private function extractPromptText(MessageBag $messageBag): string
    {
        $parts = [];
        foreach ($messageBag->getMessages() as $message) {
            if (!$message instanceof UserMessage) {
                continue;
            }
            foreach ($message->getContent() as $content) {
                if ($content instanceof Text) {
                    $parts[] = $content->getText();
                }
            }
        }

        return implode("\n", $parts);
    }

    private function fakeContractExtraction(): string
    {
        return json_encode([
            'contract_title' => 'Stub Contract',
            'contract_summary' => 'Stub summary of the contract for test purposes.',
            'contract_start_date' => '2025-01-01',
            'contract_expiration_date' => '2026-01-01',
            'jurisdiction' => 'France',
            'contract_value' => 1000,
            'contract_currency' => 'EUR',
            'renewal_period' => 1,
            'renewal_unit' => 'years',
            'parties' => [
                ['party_name' => 'TLD', 'party_role' => 'internal'],
                ['party_name' => 'Acme Corp', 'party_role' => 'client'],
            ],
            'questions' => [
                '1' => 'unclear',
                '2' => 'no',
                '3' => 'yes',
                '4' => 'none',
                '5' => 'none',
                '6' => 'none',
            ],
        ], \JSON_THROW_ON_ERROR);
    }
}
