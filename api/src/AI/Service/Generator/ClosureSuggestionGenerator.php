<?php

declare(strict_types=1);

namespace App\AI\Service\Generator;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\Service\ClosureSuggestionOutput;
use App\AI\Platform\TextPlatform;
use App\AI\Prompt\Text\ClosureSuggestionPrompt;
use App\AI\Service\AiJsonResponseParser;
use App\Entity\Activity\Comment;
use App\Entity\Service\TechnicianOnCall;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class ClosureSuggestionGenerator
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TextPlatform $platform,
        private AiJsonResponseParser $parser,
        private IriConverterInterface $iriConverter,
    ) {
    }

    public function generate(int $id): ClosureSuggestionOutput
    {
        $toc = $this->entityManager->getRepository(TechnicianOnCall::class)->find($id);

        if (null === $toc) {
            throw new NotFoundHttpException(\sprintf('TechnicianOnCall %d not found.', $id));
        }

        $comments = $this->entityManager->getRepository(Comment::class)->findBy(
            ['resource' => $this->iriConverter->getIriFromResource($toc)],
            ['createdAt' => 'ASC'],
        );

        $content = json_encode([
            'title' => $toc->title,
            'description' => $toc->description,
            'status' => $toc->status,
            'errorCodes' => $toc->errorCodes,
            'symptoms' => $toc->symptoms,
            'rootCause' => $toc->rootCause,
            'solution' => $toc->solution,
            'serviceActivity' => $toc->serviceActivity->name,
            'technicianOnCallType' => $toc->technicianOnCallType->name,
            'unitOperationalStatus' => $toc->unitOperationalStatus?->getName(),
            'comments' => array_map(
                static fn (Comment $comment): string => $comment->getMessage(),
                $comments,
            ),
        ], \JSON_THROW_ON_ERROR);

        $platformResult = $this->platform->ask(
            new ClosureSuggestionPrompt('suggest-closure/toc', []),
            $content,
            true,
        );

        $parsed = $this->parser->parse($platformResult->result);

        $output = new ClosureSuggestionOutput();
        $output->symptoms = $this->asText($parsed['symptoms'] ?? null);
        $output->rootCause = $this->asText($parsed['rootCause'] ?? null);
        $output->solution = $this->asText($parsed['solution'] ?? null);

        return $output;
    }

    private function asText(mixed $value): ?string
    {
        if (null === $value || '' === $value) {
            return null;
        }

        if (\is_array($value)) {
            $parts = array_map(
                static fn (mixed $item): string => \is_scalar($item) ? (string) $item : '',
                $value,
            );

            $text = implode("\n", array_filter($parts, static fn (string $s): bool => '' !== $s));

            return '' !== $text ? $text : null;
        }

        return \is_scalar($value) ? (string) $value : null;
    }
}
