<?php

declare(strict_types=1);

namespace App\Tests\AI\Service\Search\DMS;

use ApiPlatform\Metadata\IriConverterInterface;
use App\AI\Dto\DmsSearch;
use App\AI\Exception\NoResultException;
use App\AI\Factory\AILogFactory;
use App\AI\Service\Search\DMS\DMSSearcher;
use App\Entity\DMS;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\AI\Platform\Vector\VectorInterface;
use Symfony\AI\Store\Document\Metadata;
use Symfony\AI\Store\Document\VectorDocument;
use Symfony\AI\Store\RetrieverInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final class DMSSearcherTest extends TestCase
{
    public function testSupportsTrueForDmsSearch(): void
    {
        self::assertTrue($this->makeSearcher($this->createMock(RetrieverInterface::class))->supports(DmsSearch::class));
    }

    public function testSupportsFalseForOtherClass(): void
    {
        self::assertFalse($this->makeSearcher($this->createMock(RetrieverInterface::class))->supports(\stdClass::class));
    }

    public function testSearchThrowsNoResultExceptionWhenRetrieverReturnsNothing(): void
    {
        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([]);

        $this->expectException(NoResultException::class);
        $this->makeSearcher($retriever)->search('query');
    }

    public function testSearchReturnsSearchResultWithTitleWhenAccessGranted(): void
    {
        $dms = $this->createMock(DMS::class);
        $dms->method('getTitle')->willReturn('Technical manual v2');

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('findOneBy')->with(['legacyId' => 42])->willReturn($dms);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(DMS::class)->willReturn($repository);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('DMS_PEOPLE_VIEW_VOTER', $dms)->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')
            ->with('dms', ['m' => ['view'], 'id' => 42])
            ->willReturn('/dms/view/42');

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([$this->makeDoc(42, 0.9)]);

        $output = $this->makeSearcher($retriever, $entityManager, $security, $urlGenerator)->search('query');

        self::assertCount(1, $output->results);
        self::assertSame(42, $output->results[0]->id);
        self::assertSame('DMS', $output->results[0]->module);
        self::assertSame('Technical manual v2', $output->results[0]->description);
        self::assertSame('/dms/view/42', $output->results[0]->link);
    }

    public function testSearchReturnsConfidentialDescriptionWhenAccessDenied(): void
    {
        $dms = $this->createMock(DMS::class);
        $dms->expects(self::never())->method('getTitle');

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('findOneBy')->with(['legacyId' => 7])->willReturn($dms);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->with(DMS::class)->willReturn($repository);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->with('DMS_PEOPLE_VIEW_VOTER', $dms)->willReturn(false);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/dms/view/7');

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([$this->makeDoc(7, 0.8)]);

        $output = $this->makeSearcher($retriever, $entityManager, $security, $urlGenerator)->search('query');

        self::assertCount(1, $output->results);
        self::assertSame('Confidential', $output->results[0]->description);
    }

    public function testSearchDeduplicatesByDmsIdKeepingHighestScore(): void
    {
        $dms = $this->createMock(DMS::class);
        $dms->method('getTitle')->willReturn('Doc');

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('findOneBy')->willReturn($dms);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/dms/view/1');

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(1, 0.5), // duplicate — lower score, should be discarded
            $this->makeDoc(1, 0.9),
            $this->makeDoc(2, 0.8),
        ]);

        // Only 2 unique dms_ids => 2 results
        $output = $this->makeSearcher($retriever, $entityManager, $security, $urlGenerator)->search('query');

        self::assertCount(2, $output->results);
    }

    public function testSearchRespectsLimit(): void
    {
        $dms = $this->createMock(DMS::class);
        $dms->method('getTitle')->willReturn('Doc');

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repository->method('findOneBy')->willReturn($dms);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);

        $security = $this->createMock(Security::class);
        $security->method('isGranted')->willReturn(true);

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('/dms/view/1');

        $retriever = $this->createMock(RetrieverInterface::class);
        $retriever->method('retrieve')->willReturn([
            $this->makeDoc(1, 0.9),
            $this->makeDoc(2, 0.8),
            $this->makeDoc(3, 0.7),
            $this->makeDoc(4, 0.6),
        ]);

        $output = $this->makeSearcher($retriever, $entityManager, $security, $urlGenerator)->search('query', limit: 2);

        self::assertCount(2, $output->results);
    }

    private function makeSearcher(
        RetrieverInterface $retriever,
        ?EntityManagerInterface $entityManager = null,
        ?Security $security = null,
        ?UrlGeneratorInterface $urlGenerator = null,
    ): DMSSearcher {
        return new DMSSearcher(
            $retriever,
            $this->createMock(AILogFactory::class),
            $this->createMock(IriConverterInterface::class),
            $entityManager ?? $this->createMock(EntityManagerInterface::class),
            $security ?? $this->createMock(Security::class),
            $urlGenerator ?? $this->createMock(UrlGeneratorInterface::class),
        );
    }

    private function makeDoc(int $dmsId, float $score): VectorDocument
    {
        return (new VectorDocument(
            id: (string) $dmsId,
            vector: $this->createMock(VectorInterface::class),
            metadata: new Metadata(['meta' => ['dms_id' => $dmsId]]),
        ))->withScore($score);
    }
}
