<?php

declare(strict_types=1);

namespace AppBundle\Twig\Components\Chat;

use ApiBundle\Client;
use ApiBundle\ClientExceptionMapper;
use ApiBundle\Hydra\HydraCollection;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'chat:sidebar')]
final class Sidebar
{
    use DefaultActionTrait;

    #[LiveProp]
    public ?int $currentLogId = null;

    #[LiveProp]
    public ?string $pinError = null;

    public function __construct(
        private readonly Client $client,
        private readonly ClientExceptionMapper $exceptionMapper,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    public function getLogs(): HydraCollection|\Generator
    {
        return $this->client->findAll('/ai_logs', ['type' => 'chat'], ['pinned' => 'desc', 'id' => 'desc']);
    }

    #[LiveAction]
    public function pinConversation(#[LiveArg] int $id, #[LiveArg] bool $pinned): void
    {
        $this->pinError = null;

        try {
            $this->client->put(\sprintf('/ai_logs/%d', $id), ['json' => ['pinned' => $pinned]]);
        } catch (ClientException $e) {
            $this->pinError = $this->exceptionMapper->mapToString($e);
        }
    }

    #[LiveAction]
    public function deleteConversation(#[LiveArg] int $id): ?Response
    {
        $logs = $this->getLogs();

        $log = null;
        foreach ($logs as $item) {
            if ((int) ($item['id'] ?? 0) === $id) {
                $log = $item;
                break;
            }
        }

        if (null === $log) {
            throw new NotFoundHttpException();
        }

        $this->client->remove('ai_logs', $id);

        if ($this->currentLogId === $id) {
            return new RedirectResponse(
                $this->urlGenerator->generate('chat_home')
            );
        }

        return null;
    }
}
