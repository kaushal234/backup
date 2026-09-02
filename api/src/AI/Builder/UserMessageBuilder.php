<?php

declare(strict_types=1);

namespace App\AI\Builder;

use App\AI\Service\FileTextExtractor;
use Symfony\AI\Platform\Message\Content\DocumentUrl;
use Symfony\AI\Platform\Message\Content\File;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\UserMessage;

class UserMessageBuilder
{
    public function __construct(
        private readonly FileTextExtractor $textExtractor,
    ) {
    }

    /**
     * Extracts the original user prompt from a UserMessage built by this
     * builder. Relies on the invariant that build() always puts the
     * prompt as the first Text content.
     */
    public static function extractPrompt(UserMessage $message): string
    {
        foreach ($message->getContent() as $part) {
            if ($part instanceof Text) {
                return $part->getText();
            }
        }

        return '';
    }

    public function build(string $text, ?string $filepath = null): UserMessage
    {
        $contents = [new Text($text)];

        if (null === $filepath) {
            return Message::ofUser(...$contents);
        }

        $extracted = $this->textExtractor->extract($filepath);
        if (null !== $extracted && '' !== mb_trim($extracted)) {
            $contents[] = new Text(\sprintf("[Attached file content]\n%s", $extracted));

            return Message::ofUser(...$contents);
        }

        // Fallback for non text-extractable files (images, archives, …):
        // keep inlining as a base64 data URL so Mistral can still process them.
        $contents[] = new DocumentUrl(File::fromFile($filepath)->asDataUrl());

        return Message::ofUser(...$contents);
    }
}
