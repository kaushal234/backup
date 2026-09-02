<?php

declare(strict_types=1);

namespace App\AI\Filterable\Definition\News;

use App\AI\Filterable\Definition\AbstractFilterableDefinition;
use App\AI\Filterable\Definition\Filter;
use App\Entity\News\News;

final readonly class NewsFilterable extends AbstractFilterableDefinition
{
    public function name(): string
    {
        return 'news';
    }

    public function entityClass(): string
    {
        return News::class;
    }

    public function defaultOrder(): array
    {
        return ['date' => 'DESC'];
    }

    public function description(): string
    {
        return 'Internal company news articles published on the intranet, including banners and major incident announcements. ';
    }

    public function fields(): array
    {
        return [
            Filter::like('titleLike', 'title', desc: 'Partial title (LIKE %value%).'),
            Filter::like('contentLike', 'content', desc: 'Partial content (LIKE %value%).'),
            Filter::in('categoryNames', 'category.name', desc: 'Exact news category names. Multiple = OR.'),
            Filter::inInt('authorPeopleIds', 'people', desc: 'IDs of authors (People).'),
            Filter::in('departmentNames', 'department.name', desc: 'Exact department names.'),
            Filter::in('divisionNames', 'division.name', desc: 'Exact division names.'),
            Filter::in('premiseNames', 'premise.name', desc: 'Exact premise (site) names.'),
            Filter::bool('banner', 'banner', desc: 'True = only banner news, false = only non-banner, omit for both.'),
            Filter::bool('majorIncident', 'majorIncident', desc: 'True = only major incidents, false = only non-incidents, omit for both.'),
            Filter::dateRange('date', afterName: 'dateAfter', beforeName: 'dateBefore', afterDesc: 'ISO-8601 date — news published on/after this date.', beforeDesc: 'ISO-8601 date — news published on/before this date.'),
        ];
    }

    public function summarize(object $entity): array
    {
        $entity = $this->ensureInstance($entity, News::class);

        return [
            'id' => $entity->getId(),
            'title' => $entity->getTitle(),
            'contentShort' => $entity->getContentShort(),
            'category' => $entity->getCategory()?->getName(),
            'author' => $this->personName($entity->getPeople()),
            'department' => $entity->department?->getName(),
            'division' => $entity->division?->name,
            'premise' => $entity->premise?->name,
            'banner' => $entity->isBanner(),
            'bannerText' => $entity->getBannerText(),
            'majorIncident' => $entity->isMajorIncident(),
            'date' => $entity->getDate()->format(\DATE_ATOM),
        ];
    }
}
