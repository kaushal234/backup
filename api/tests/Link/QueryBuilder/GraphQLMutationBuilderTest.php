<?php

declare(strict_types=1);

namespace App\Tests\Link\QueryBuilder;

use App\Entity\EquipmentRecord;
use App\Entity\Sales\Demo;
use App\Link\Mapping\Mapper\FieldMapper;
use App\Link\QueryBuilder\GraphQLMutationBuilder;
use App\Link\ResourceSourceProvider\Support\EquipmentRecordResourceSourceProvider;
use App\Link\SourceProvider\SourceProvider;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class GraphQLMutationBuilderTest extends TestCase
{
    use ProphecyTrait;

    public function testMutationIsCorrectlyConstructedWithoutLinkId()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $fieldMapperProphecy = $this->prophesize(FieldMapper::class);
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);

        $equipmentRecordSourceProvider = $this->prophesize(EquipmentRecordResourceSourceProvider::class);

        $sourceProviderProphecy->getResourceSourceProvider(EquipmentRecord::class)->shouldBeCalledOnce()->willReturn($equipmentRecordSourceProvider->reveal());

        $mapping = [
            'serialNumber' => [
                'fields' => ['identifier', 'plateNumber', 'astusId'],
                'transformer' => [],
                'options' => [],
            ],
            'firstGreenTagDate' => [
                'fields' => ['firstGreenTagDate'],
                'transformer' => [],
                'options' => [],
            ],
        ];

        $fieldMapperProphecy->getMapping(new \ReflectionClass(EquipmentRecord::class))->shouldBeCalledOnce()->willReturn($mapping);

        $normalizerProphecy->normalize($equipmentRecord = new EquipmentRecord(), null, [AbstractNormalizer::GROUPS => [GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP]])->shouldBeCalledOnce()->willReturn(['serialNumber' => 'T13000']);

        $equipmentRecordSourceProvider->getService()->shouldBeCalledOnce()->willReturn('foo');
        $equipmentRecordSourceProvider->getReturnedFields()->shouldBeCalledOnce()->willReturn('bar foobar');

        $queryBuilder = new GraphQLMutationBuilder($sourceProviderProphecy->reveal(), $fieldMapperProphecy->reveal(), $normalizerProphecy->reveal(), []);

        self::assertSame(str_replace(["\n", "\r", ' '], '', <<<'GRAPHQL'
            mutation {
              foo_save(params:
                {
                    fieldsAndValues: {identifier:"T13000",plateNumber:"T13000",astusId:"T13000"}
                }
              ) {bar foobar}
            }
            GRAPHQL),
            str_replace(["\n", "\r", ' '], '', $queryBuilder->getMutation($equipmentRecord)));
    }

    public function testMutationIsCorrectlyConstructedWithLinkId()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $fieldMapperProphecy = $this->prophesize(FieldMapper::class);
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);

        $equipmentRecordSourceProvider = $this->prophesize(EquipmentRecordResourceSourceProvider::class);

        $sourceProviderProphecy->getResourceSourceProvider(EquipmentRecord::class)->shouldBeCalledOnce()->willReturn($equipmentRecordSourceProvider->reveal());

        $mapping = [
            'serialNumber' => [
                'fields' => ['identifier', 'plateNumber', 'astusId'],
                'transformer' => [],
                'options' => [],
            ],
            'firstGreenTagDate' => [
                'fields' => ['firstGreenTagDate'],
                'transformer' => [],
                'options' => [],
            ],
        ];

        $fieldMapperProphecy->getMapping(new \ReflectionClass(EquipmentRecord::class))->shouldBeCalledOnce()->willReturn($mapping);

        $normalizerProphecy->normalize($equipmentRecord = (new EquipmentRecord())->setLinkId(13), null, [AbstractNormalizer::GROUPS => [GraphQLMutationBuilder::LINK_NORMALIZATION_GROUP]])->shouldBeCalledOnce()->willReturn(['serialNumber' => 'T13000']);

        $equipmentRecordSourceProvider->getService()->shouldBeCalledOnce()->willReturn('foo');
        $equipmentRecordSourceProvider->getReturnedFields()->shouldBeCalledOnce()->willReturn('bar foobar');

        $queryBuilder = new GraphQLMutationBuilder($sourceProviderProphecy->reveal(), $fieldMapperProphecy->reveal(), $normalizerProphecy->reveal(), []);

        self::assertSame(<<<'GRAPHQL'
            mutation {
              foo_save(params:
                {
                    id: 13,
                    fieldsAndValues: {identifier:"T13000",plateNumber:"T13000",astusId:"T13000"}
                }
              ) {bar foobar}
            }
            GRAPHQL,
            $queryBuilder->getMutation($equipmentRecord));
    }

    public function testArchiveMutationIsCorrectlyConstructed()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $fieldMapperProphecy = $this->prophesize(FieldMapper::class);
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);

        $equipmentRecordSourceProvider = $this->prophesize(EquipmentRecordResourceSourceProvider::class);

        $sourceProviderProphecy->getResourceSourceProvider(EquipmentRecord::class)->shouldBeCalledOnce()->willReturn($equipmentRecordSourceProvider->reveal());
        $equipmentRecordSourceProvider->getService()->shouldBeCalledOnce()->willReturn('foo');
        $equipmentRecordSourceProvider->getReturnedFields()->shouldBeCalledOnce()->willReturn('bar foobar');

        $queryBuilder = new GraphQLMutationBuilder($sourceProviderProphecy->reveal(), $fieldMapperProphecy->reveal(), $normalizerProphecy->reveal(), []);

        self::assertSame(<<<'GRAPHQL'
            mutation {
              foo_save(params:
                {
                    id: 3,
                    fieldsAndValues: {archived:true}
                }
              ) {bar foobar}
            }
            GRAPHQL,
            $queryBuilder->getArchiveMutation((new EquipmentRecord())->setLinkId(3)));
    }

    public function testExceptionIsThrownWhenResourceIsNotLinkInterfaceOnMutation()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $fieldMapperProphecy = $this->prophesize(FieldMapper::class);
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);

        $queryBuilder = new GraphQLMutationBuilder($sourceProviderProphecy->reveal(), $fieldMapperProphecy->reveal(), $normalizerProphecy->reveal(), []);

        self::expectException(UnprocessableEntityHttpException::class);
        self::expectExceptionMessage('Object should be an instance of LinkResourceInterface.');

        $queryBuilder->getMutation(new Demo());
    }

    public function testExceptionIsThrownWhenResourceIsNotLinkInterfaceOnArchiveMutation()
    {
        $sourceProviderProphecy = $this->prophesize(SourceProvider::class);
        $fieldMapperProphecy = $this->prophesize(FieldMapper::class);
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);

        $queryBuilder = new GraphQLMutationBuilder($sourceProviderProphecy->reveal(), $fieldMapperProphecy->reveal(), $normalizerProphecy->reveal(), []);

        self::expectException(UnprocessableEntityHttpException::class);
        self::expectExceptionMessage('Object should be an instance of LinkResourceInterface and have linkId property set.');

        $queryBuilder->getArchiveMutation(new Demo());
    }
}
