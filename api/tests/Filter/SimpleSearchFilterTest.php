<?php

declare(strict_types=1);

namespace App\Tests\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use App\Entity\MIS\GuestUser\GuestUser;
use App\Entity\Sales\Customer;
use App\Entity\Sales\ForecastClosure;
use App\Filter\SimpleSearchFilter;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SimpleSearchFilterTest extends KernelTestCase
{
    use ProphecyTrait;

    private ManagerRegistry $managerRegistry;

    protected function setUp(): void
    {
        self::bootKernel();
        /** @var ManagerRegistry $managerRegistry */
        $managerRegistry = static::getContainer()->get('doctrine');
        $this->managerRegistry = $managerRegistry;
    }

    public function testSimpleSearchFilterWithUniqueWord()
    {
        $resourceClass = ForecastClosure::class;
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('o');
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = $iriConverterProphecy->reveal();
        $identifierExtractorProphecy = $this->prophesize(IdentifiersExtractorInterface::class);
        /** @var IdentifiersExtractorInterface $identifierExtractor */
        $identifierExtractor = $identifierExtractorProphecy->reveal();

        $filter = new SimpleSearchFilter($this->managerRegistry, $iriConverter, null, null, ['comment' => 'partial', 'salesForecast.endUser.name' => 'partial'], $identifierExtractor);

        $filter->apply($queryBuilder, new QueryNameGenerator(), $resourceClass, new GetCollection(), ['filters' => ['q' => 'foo']]);

        $expected = \sprintf('SELECT o
FROM %s o
LEFT JOIN o.salesForecast salesForecast_a1
LEFT JOIN salesForecast_a1.endUser endUser_a2
WHERE o.comment LIKE :comment_p1 OR endUser_a2.name LIKE :name_p2', $resourceClass);

        self::assertSame($this->toDQLString($expected), $queryBuilder->getQuery()->getDQL());
        self::assertSame('%foo%', $queryBuilder->getParameter('comment_p1')->getValue());
        self::assertSame('%foo%', $queryBuilder->getParameter('name_p2')->getValue());
    }

    public function testSimpleSearchFilterWithDistinctWords()
    {
        $resourceClass = ForecastClosure::class;
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('o');
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverter = $iriConverterProphecy->reveal();
        $identifierExtractorProphecy = $this->prophesize(IdentifiersExtractorInterface::class);
        /** @var IdentifiersExtractorInterface $identifierExtractor */
        $identifierExtractor = $identifierExtractorProphecy->reveal();

        $filter = new SimpleSearchFilter($this->managerRegistry, $iriConverter, null, null, ['comment' => 'partial', 'salesForecast.endUser.name' => 'partial'], $identifierExtractor);

        $filter->apply($queryBuilder, new QueryNameGenerator(), $resourceClass, new GetCollection(), ['filters' => ['q' => 'foo bar']]);

        $expected = \sprintf('SELECT o
FROM %s o
LEFT JOIN o.salesForecast salesForecast_a1
LEFT JOIN salesForecast_a1.endUser endUser_a2
WHERE (o.comment LIKE :comment_p1 OR endUser_a2.name LIKE :name_p2) AND (o.comment LIKE :comment_p3 OR endUser_a2.name LIKE :name_p4)', $resourceClass);

        self::assertSame($this->toDQLString($expected), $queryBuilder->getQuery()->getDQL());
        self::assertSame('%foo%', $queryBuilder->getParameter('comment_p1')->getValue());
        self::assertSame('%bar%', $queryBuilder->getParameter('comment_p3')->getValue());
        self::assertSame('%foo%', $queryBuilder->getParameter('name_p2')->getValue());
        self::assertSame('%bar%', $queryBuilder->getParameter('name_p4')->getValue());
    }

    public function testExistingLeftJoinAreNotDuplicated()
    {
        $resourceClass = ForecastClosure::class;
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('o');
        $queryBuilder->leftJoin('o.salesForecast', 'custom_alias');

        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverter = $iriConverterProphecy->reveal();
        $identifierExtractorProphecy = $this->prophesize(IdentifiersExtractorInterface::class);
        /** @var IdentifiersExtractorInterface $identifierExtractor */
        $identifierExtractor = $identifierExtractorProphecy->reveal();

        $filter = new SimpleSearchFilter($this->managerRegistry, $iriConverter, null, null, ['comment' => 'partial', 'salesForecast.endUser.name' => 'partial'], $identifierExtractor);

        $filter->apply($queryBuilder, new QueryNameGenerator(), $resourceClass, new GetCollection(), ['filters' => ['q' => 'foo bar']]);

        $expected = \sprintf('SELECT o
FROM %s o
LEFT JOIN o.salesForecast custom_alias
LEFT JOIN custom_alias.endUser endUser_a1
WHERE (o.comment LIKE :comment_p1 OR endUser_a1.name LIKE :name_p2) AND (o.comment LIKE :comment_p3 OR endUser_a1.name LIKE :name_p4)', $resourceClass);

        self::assertSame($this->toDQLString($expected), $queryBuilder->getQuery()->getDQL());
        self::assertSame('%foo%', $queryBuilder->getParameter('comment_p1')->getValue());
        self::assertSame('%bar%', $queryBuilder->getParameter('comment_p3')->getValue());
        self::assertSame('%foo%', $queryBuilder->getParameter('name_p2')->getValue());
        self::assertSame('%bar%', $queryBuilder->getParameter('name_p4')->getValue());
    }

    public function testThatJoinConditionsRequiredForSearchAlwaysUseLeftJoin()
    {
        $resourceClass = Customer::class;
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('o');
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        $iriConverter = $iriConverterProphecy->reveal();
        $identifierExtractorProphecy = $this->prophesize(IdentifiersExtractorInterface::class);
        /** @var IdentifiersExtractorInterface $identifierExtractor */
        $identifierExtractor = $identifierExtractorProphecy->reveal();

        $filter = new SimpleSearchFilter($this->managerRegistry, $iriConverter, null, null, ['name' => 'start', 'country.name' => 'end'], $identifierExtractor);
        $filter->apply($queryBuilder, new QueryNameGenerator(), $resourceClass, new GetCollection(), ['filters' => ['q' => 'baz']]);

        $expected = \sprintf('SELECT o
FROM %s o
LEFT JOIN o.country country_a1
WHERE o.name LIKE :name_p1 OR country_a1.name LIKE :name_p2', $resourceClass);

        self::assertSame($this->toDQLString($expected), $queryBuilder->getQuery()->getDQL());
        self::assertSame('baz%', $queryBuilder->getParameter('name_p1')->getValue());
        self::assertSame('%baz', $queryBuilder->getParameter('name_p2')->getValue());
    }

    public function testSimpleSearchFilterOnGuestUser()
    {
        $resourceClass = GuestUser::class;
        /** @var EntityRepository $repository */
        $repository = $this->managerRegistry->getRepository($resourceClass);
        $queryBuilder = $repository->createQueryBuilder('o');
        $iriConverterProphecy = $this->prophesize(IriConverterInterface::class);
        /** @var IriConverterInterface $iriConverter */
        $iriConverter = $iriConverterProphecy->reveal();
        $identifierExtractorProphecy = $this->prophesize(IdentifiersExtractorInterface::class);
        /** @var IdentifiersExtractorInterface $identifierExtractor */
        $identifierExtractor = $identifierExtractorProphecy->reveal();

        $filter = new SimpleSearchFilter($this->managerRegistry, $iriConverter, null, null, ['firstname' => 'partial', 'lastname' => 'partial', 'email' => 'partial', 'username' => 'partial'], $identifierExtractor);

        $filter->apply($queryBuilder, new QueryNameGenerator(), $resourceClass, new GetCollection(), ['filters' => ['q' => 'foo']]);

        $expected = \sprintf('SELECT o
FROM %s o
WHERE o.firstname LIKE :firstname_p1 OR o.lastname LIKE :lastname_p2 OR o.email LIKE :email_p3 OR o.username LIKE :username_p4', $resourceClass);

        self::assertSame($this->toDQLString($expected), $queryBuilder->getQuery()->getDQL());
        self::assertSame('%foo%', $queryBuilder->getParameter('firstname_p1')->getValue());
        self::assertSame('%foo%', $queryBuilder->getParameter('lastname_p2')->getValue());
        self::assertSame('%foo%', $queryBuilder->getParameter('email_p3')->getValue());
        self::assertSame('%foo%', $queryBuilder->getParameter('username_p4')->getValue());
    }

    private function toDQLString(string $dql): string
    {
        return preg_replace(['/\s+/', '/\(\s/', '/\s\)/'], [' ', '(', ')'], $dql);
    }
}
