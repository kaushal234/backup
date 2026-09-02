<?php

declare(strict_types=1);

namespace App\DataCollector;

use App\Entity\Activity\Comment;
use App\Entity\Activity\Log;
use Symfony\Bundle\FrameworkBundle\DataCollector\AbstractDataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActivityCollector extends AbstractDataCollector
{
    private readonly NormalizerInterface $normalizer;

    public function __construct(NormalizerInterface $normalizer)
    {
        $this->normalizer = $normalizer;
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, ?\Throwable $exception = null): void
    {
        // do nothing
    }

    public function addLog(Log $log)
    {
        $this->data['logs'][] = $log;
    }

    /**
     * @return array|Log[]
     */
    public function getLogs(): array
    {
        return $this->data['logs'] ?? [];
    }

    public function addComment(Comment $comment)
    {
        $this->data['comments'][] = $this->normalizer->normalize($comment, 'jsonld', ['groups' => ['activity', 'people_public']]);
    }

    /**
     * @return array|Comment[]
     */
    public function getComments(): array
    {
        return $this->data['comments'];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'app.activity_collector';
    }

    public function reset(): void
    {
        $this->data = [];
    }
}
