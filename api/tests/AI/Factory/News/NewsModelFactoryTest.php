<?php

declare(strict_types=1);

namespace App\Tests\AI\Factory\News;

use App\AI\Factory\Directory\UserModelFactory;
use App\AI\Factory\News\NewsModelFactory;
use App\Entity\Directory\Department;
use App\Entity\Directory\Division;
use App\Entity\Directory\People;
use App\Entity\News\News;
use App\Entity\News\NewsCategory;
use PHPUnit\Framework\TestCase;

final class NewsModelFactoryTest extends TestCase
{
    public function testSupports(): void
    {
        $factory = $this->makeFactory();

        self::assertTrue($factory->supports(News::class));
        self::assertFalse($factory->supports(\stdClass::class));
    }

    public function testCreateMapsScalarFieldsWithMinimalEntity(): void
    {
        $entity = $this->createMock(News::class);
        $entity->method('getTitle')->willReturn('a title');
        $entity->method('getContent')->willReturn('a content');
        $entity->method('getContentShort')->willReturn('short');
        $entity->method('getBannerText')->willReturn(null);
        $entity->method('isMajorIncident')->willReturn(false);
        $entity->method('isBanner')->willReturn(false);
        $entity->method('getDate')->willReturn(new \DateTime('2025-04-01'));
        $entity->method('getCategory')->willReturn(null);
        $entity->method('getPeople')->willReturn(null);

        $model = $this->makeFactory()->create($entity);

        self::assertSame('a title', $model->title);
        self::assertSame('a content', $model->content);
        self::assertSame('short', $model->contentShort);
        self::assertNull($model->bannerText);
        self::assertFalse($model->majorIncident);
        self::assertFalse($model->banner);
        self::assertSame('2025-04-01', $model->date->format('Y-m-d'));
        self::assertNull($model->category);
        self::assertNull($model->people);
        self::assertNull($model->department);
        self::assertNull($model->division);
        self::assertNull($model->premise);
    }

    public function testCreateMapsRelations(): void
    {
        $category = $this->createMock(NewsCategory::class);
        $category->method('getName')->willReturn('SAFETY');

        $people = $this->createMock(People::class);
        $people->method('getUsername')->willReturn('alice');
        $people->method('getEmail')->willReturn('alice@x.test');
        $people->method('getFirstname')->willReturn('Alice');
        $people->method('getLastname')->willReturn('X');

        $department = $this->createMock(Department::class);
        $department->method('getName')->willReturn('QA');
        $department->method('isSso')->willReturn(true);
        $department->method('isFactory')->willReturn(false);

        $division = new Division();
        $division->name = 'EMEA';

        $entity = $this->createMock(News::class);
        $entity->method('getTitle')->willReturn('a title');
        $entity->method('getContent')->willReturn('a content');
        $entity->method('getContentShort')->willReturn('short');
        $entity->method('getBannerText')->willReturn('banner');
        $entity->method('isMajorIncident')->willReturn(true);
        $entity->method('isBanner')->willReturn(true);
        $entity->method('getDate')->willReturn(new \DateTime('2025-04-01'));
        $entity->method('getCategory')->willReturn($category);
        $entity->method('getPeople')->willReturn($people);
        $entity->department = $department;
        $entity->division = $division;

        $model = $this->makeFactory()->create($entity);

        self::assertNotNull($model->category);
        self::assertSame('SAFETY', $model->category->name);
        self::assertNotNull($model->people);
        self::assertSame('alice', $model->people->username);
        self::assertNotNull($model->department);
        self::assertSame('QA', $model->department->name);
        self::assertTrue($model->department->sso);
        self::assertFalse($model->department->factory);
        self::assertNotNull($model->division);
        self::assertSame('EMEA', $model->division->name);
    }

    private function makeFactory(): NewsModelFactory
    {
        return new NewsModelFactory(new UserModelFactory());
    }
}
