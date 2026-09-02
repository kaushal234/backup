<?php

declare(strict_types=1);

namespace App\AI\Service;

use Alvest\FeatureDoc\Attribute\FeatureDoc;
use App\AI\Dto\Activity\CommentModel;
use App\AI\Dto\Activity\RelatedEntityModel;
use App\AI\Service\Loader\Comment\CommentLoader;
use App\AI\Service\Loader\Comment\LegacyCommentLoader;
use App\AI\Service\Loader\RelatedEntity\RelatedEntityLoader;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @phpstan-type CommentConfig array{type: 'default'|'legacy', legacy_module: string|null}
 * @phpstan-type RelatedEntityConfig array{parent_id: 'id'|'legacy_id', module: string}
 * @phpstan-type EntityConfig array{class: class-string|null, comments?: CommentConfig, related_entities?: RelatedEntityConfig}
 */
#[FeatureDoc(path: 'ai-comment-source.md')]
final readonly class AIEntityRegistry
{
    /**
     * @param array<string, EntityConfig> $config
     */
    public function __construct(
        #[Autowire(param: 'alvest_ai.entities')]
        private array $config,
        private CommentLoader $commentLoader,
        private LegacyCommentLoader $legacyCommentLoader,
        private RelatedEntityLoader $relatedEntityLoader,
    ) {
    }

    public function getEntityClass(string $slug): string
    {
        $class = $this->getEntry($slug)['class'];
        if (null === $class) {
            throw new \InvalidArgumentException(\sprintf('Entity "%s" has no class configured.', $slug));
        }

        return $class;
    }

    /**
     * @return CommentModel[]
     */
    public function findCommentsFor(string $slug, object $entity): array
    {
        $entry = $this->getEntry($slug);
        $comments = $entry['comments'] ?? null;
        if (null === $comments) {
            throw new \InvalidArgumentException(\sprintf('Entity "%s" has no comment source configured.', $slug));
        }

        if ('default' === $comments['type']) {
            return $this->commentLoader->findComments($entity);
        }

        \assert(null !== $comments['legacy_module']);

        return $this->legacyCommentLoader->findComments($entity, $comments['legacy_module']);
    }

    /**
     * @return RelatedEntityModel[]
     */
    public function findRelatedEntitiesFor(string $slug, object $entity): array
    {
        $entry = $this->getEntry($slug);
        $related = $entry['related_entities'] ?? null;
        if (null === $related) {
            throw new \InvalidArgumentException(\sprintf('Entity "%s" has no related-entity source configured.', $slug));
        }

        return $this->relatedEntityLoader->findRelatedEntities($entity, $related['parent_id'], $related['module']);
    }

    /**
     * @return EntityConfig
     */
    private function getEntry(string $slug): array
    {
        if (!isset($this->config[$slug])) {
            throw new \InvalidArgumentException(\sprintf('Unknown AI entity slug "%s".', $slug));
        }

        return $this->config[$slug];
    }
}
