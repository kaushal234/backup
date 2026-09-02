<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\ORM\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use App\Doctrine\ORM\Extension\EmissionRatingExtension;
use App\Entity\EmissionRating;
use Doctrine\ORM\QueryBuilder;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class EmissionRatingExtensionTest extends TestCase
{
    use ProphecyTrait;

    public function testEmissionRatingAreFiltered()
    {
        $extension = new EmissionRatingExtension();

        self::assertInstanceOf(QueryCollectionExtensionInterface::class, $extension);
        self::assertNotInstanceOf(QueryItemExtensionInterface::class, $extension);

        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderProphecy->getRootAliases()->willReturn(['o'])->shouldBeCalledTimes(1);
        $queryBuilderProphecy->andWhere('o.obsolete = :value')->willReturn($queryBuilderProphecy)->shouldBeCalledTimes(1);
        $queryBuilderProphecy->setParameter('value', false)->willReturn($queryBuilderProphecy)->shouldBeCalledTimes(1);

        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $queryNameGeneratorProphecy->generateParameterName()->shouldNotBeCalled();

        $extension->applyToCollection($queryBuilderProphecy->reveal(), $queryNameGeneratorProphecy->reveal(), EmissionRating::class, new GetCollection());
    }

    /** @dataProvider extensionDisablingValuesGenerator */
    public function testEmissionRatingFilteringisNotFiltering(string $resourceClass, Operation $operation, array $context)
    {
        $extension = new EmissionRatingExtension();

        $queryBuilderProphecy = $this->prophesize(QueryBuilder::class);
        $queryBuilderProphecy->getRootAliases()->shouldNotBeCalled();

        $queryNameGeneratorProphecy = $this->prophesize(QueryNameGeneratorInterface::class);
        $queryNameGeneratorProphecy->generateParameterName()->shouldNotBeCalled();

        $extension->applyToCollection($queryBuilderProphecy->reveal(), $queryNameGeneratorProphecy->reveal(), $resourceClass, $operation, $context);
    }

    public function extensionDisablingValuesGenerator(): \Generator
    {
        yield 'bad resource class' => ['Foo', new GetCollection(), []];
        yield 'bad operation name' => [EmissionRating::class, new Get(), []];
        yield 'specific group in context' => [EmissionRating::class, new GetCollection(), ['groups' => ['foo', 'show_obsolete', 'bar']]];
    }
}
