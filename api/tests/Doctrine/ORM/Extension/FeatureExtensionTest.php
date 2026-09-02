<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use App\Doctrine\ORM\Extension\FeatureExtension;
use App\Entity\Feature;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class FeatureExtensionTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideContext
     */
    public function testOnlyDistinctResultsAreFetchedDependingOnTheContext(array $context, bool $distinctIsCalled)
    {
        $extension = new FeatureExtension();

        $queryBuilderProphecy = $this->createMock(QueryBuilder::class);
        $queryBuilderProphecy->expects($this->exactly((int) $distinctIsCalled))->method('distinct');

        $extension->applyToCollection($queryBuilderProphecy, new QueryNameGenerator(), Feature::class, new GetCollection(), $context);
    }

    public function provideContext()
    {
        yield 'Query builder is not updated if the normalization groups are not overridden' => [[], false];
        yield 'Query builder distinct method is called if the normalization groups are overridden with "feature_list"' => [['filters' => ['normalization_groups_override' => ['feature_list']]], true];
    }
}
