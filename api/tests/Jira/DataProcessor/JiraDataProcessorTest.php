<?php

declare(strict_types=1);

namespace App\Tests\Jira\DataProcessor;

use ApiPlatform\Metadata\Post;
use App\Jira\DataProcessor\JiraDataProcessor;
use App\Jira\Http\JiraClientInterface;
use App\Jira\Mapping\Mapper\FieldMapper;
use App\Jira\Resolver\JiraClientResolver;
use App\Jira\ResourceSourceProvider\ResourceSourceProviderInterface;
use App\Jira\SourceProvider\SourceProvider;
use App\Tests\Jira\Entity\JiraCommentResourceDummy;
use App\Tests\Jira\Entity\JiraIssueByKeyMainObjectDummy;
use App\Tests\Jira\Entity\JiraIssueResourceDummy;
use App\Tests\Jira\Entity\UppercaseTransformerDummy;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Workflow\Registry;

class JiraDataProcessorTest extends TestCase
{
    use ProphecyTrait;

    public function testProcessCommentPostsTransformedPayloadAndReturnsRawResponseWithoutDenormalizing(): void
    {
        $data = new JiraCommentResourceDummy('AIRB-777');
        $data->body = 'hello';

        $operation = new Post(name: 'jira_tracteasy_post_comment');

        $rawJiraResponse = ['id' => '10001', 'body' => ['type' => 'doc', 'version' => 1, 'content' => []]];

        $clientProphecy = $this->prophesize(JiraClientInterface::class);
        $clientProphecy
            ->doRequest('issue/AIRB-777/comment', null, ['json' => ['body' => 'HELLO']], Request::METHOD_POST)
            ->shouldBeCalledOnce()
            ->willReturn(new Response((string) json_encode($rawJiraResponse)));

        $clientResolverProphecy = $this->prophesize(JiraClientResolver::class);
        $clientResolverProphecy->resolve($operation)->willReturn($clientProphecy->reveal());

        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $denormalizerProphecy->denormalize(Argument::cetera())->shouldNotBeCalled();

        $processor = $this->createProcessor($clientResolverProphecy->reveal(), denormalizer: $denormalizerProphecy->reveal());

        $result = $processor->process($data, $operation);

        self::assertSame($rawJiraResponse, $result);
    }

    public function testProcessCommentPassesThroughUnmappedProperties(): void
    {
        $data = new JiraCommentResourceDummy('AIRB-42');
        $data->body = 'body';
        $data->author = 'author';

        $operation = new Post(name: 'jira_tracteasy_post_comment');

        $clientProphecy = $this->prophesize(JiraClientInterface::class);
        $clientProphecy
            ->doRequest('issue/AIRB-42/comment', null, ['json' => ['body' => 'BODY', 'author' => 'author']], Request::METHOD_POST)
            ->shouldBeCalledOnce()
            ->willReturn(new Response((string) json_encode(['id' => '1'])));

        $clientResolverProphecy = $this->prophesize(JiraClientResolver::class);
        $clientResolverProphecy->resolve($operation)->willReturn($clientProphecy->reveal());

        $processor = $this->createProcessor($clientResolverProphecy->reveal());

        $processor->process($data, $operation);
    }

    public function testProcessBuildsProjectKeyPayloadAndDenormalizesResponse(): void
    {
        $data = new JiraIssueResourceDummy(mainClassId: 7);
        $data->summary = 'equipmentRecord issue on the field';
        $data->description = 'Any raw text for testing';

        $mainObject = new JiraIssueByKeyMainObjectDummy('AIRB');
        $operation = new Post(name: 'jira_tracteasy_post_issue');

        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $resourceSourceProviderProphecy = $this->prophesize(ResourceSourceProviderInterface::class);
        $resourceSourceProviderProphecy->getMainClass()->willReturn(JiraIssueByKeyMainObjectDummy::class);
        $resourceSourceProviderProphecy->getWriteOperation()->willReturn('issue');
        $sourceProviderProphecy->getResourceSourceProvider(JiraIssueResourceDummy::class)
            ->willReturn($resourceSourceProviderProphecy->reveal());

        $repositoryProphecy = $this->prophesize(EntityRepository::class);
        $repositoryProphecy->find(7)->willReturn($mainObject);

        $entityManagerProphecy = $this->prophesize(EntityManagerInterface::class);
        $entityManagerProphecy->getRepository(JiraIssueByKeyMainObjectDummy::class)
            ->willReturn($repositoryProphecy->reveal());

        $expectedPayload = [
            'project' => ['key' => 'AIRB'],
            'summary' => 'EQUIPMENTRECORD ISSUE ON THE FIELD',
            'description' => 'Any raw text for testing',
        ];

        $clientProphecy = $this->prophesize(JiraClientInterface::class);
        $clientProphecy
            ->doRequest('issue', null, ['json' => ['fields' => $expectedPayload]], Request::METHOD_POST)
            ->willReturn(new Response((string) json_encode(['id' => '42'])));

        $clientResolverProphecy = $this->prophesize(JiraClientResolver::class);
        $clientResolverProphecy->resolve($operation)->willReturn($clientProphecy->reveal());

        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);
        $denormalizerProphecy->denormalize(['id' => '42'], JiraIssueResourceDummy::class, null, [])
            ->willReturn('denormalized-issue');

        $processor = $this->createProcessor(
            $clientResolverProphecy->reveal(),
            $sourceProviderProphecy->reveal(),
            $entityManagerProphecy->reveal(),
            $denormalizerProphecy->reveal(),
        );

        self::assertSame('denormalized-issue', $processor->process($data, $operation));
    }

    private function createProcessor(
        JiraClientResolver $clientResolver,
        ?SourceProvider $sourceProvider = null,
        ?EntityManagerInterface $entityManager = null,
        ?DenormalizerInterface $denormalizer = null,
    ): JiraDataProcessor {
        return new JiraDataProcessor(
            $clientResolver,
            $sourceProvider ?? $this->prophesize(SourceProvider::class)->reveal(),
            new FieldMapper(),
            $denormalizer ?? $this->prophesize(DenormalizerInterface::class)->reveal(),
            $entityManager ?? $this->prophesize(EntityManagerInterface::class)->reveal(),
            [new UppercaseTransformerDummy()],
            $this->prophesize(Registry::class)->reveal(),
        );
    }
}
