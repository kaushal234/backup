<?php

declare(strict_types=1);

namespace App\AI\Platform;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Factory\AILogFactory;
use App\AI\Platform\Invoker\PlatformInvokerInterface;
use App\AI\Prompt\PromptInterface;
use App\AI\Service\FileTextExtractor;
use Symfony\AI\Platform\Message\Content\DocumentUrl;
use Symfony\AI\Platform\Message\Content\File;
use Symfony\AI\Platform\Message\Content\Text;
use Symfony\AI\Platform\Message\Message;
use Symfony\AI\Platform\Message\MessageBag;

readonly class DocumentPlatform extends AbstractPlatform
{
    public function __construct(
        PlatformInvokerInterface $invoker,
        AILogFactory $factory,
        IriConverterInterface $iriConverter,
        private FileTextExtractor $textExtractor,
    ) {
        parent::__construct($invoker, $factory, $iriConverter);
    }

    public function ask(PromptInterface $prompt, string $filepath, bool $createLog = false, string $model = 'mistral-small-latest'): PlatformResult
    {
        return $this->invoke(
            messageBag: new MessageBag(Message::ofUser(
                new Text($prompt->instructions()),
                $this->buildFileContent($filepath),
            )),
            source: $prompt->getSource(),
            options: $prompt->getOptions(),
            model: $model,
            createLog: $createLog,
        );
    }

    private function buildFileContent(string $filepath): Text|DocumentUrl
    {
        $extracted = $this->textExtractor->extract($filepath);
        if (null !== $extracted && '' !== mb_trim($extracted)) {
            return new Text(\sprintf("[Attached file content]\n%s", $extracted));
        }

        // Fallback for non text-extractable files (images, archives, …):
        // inline as a base64 data URL so Mistral can still process them.
        return new DocumentUrl(File::fromFile($filepath)->asDataUrl());
    }
}
