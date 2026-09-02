<?php

declare(strict_types=1);

namespace App\Tests\Serializer\Normalizer;

use App\Entity\Activity\Comment;
use App\Serializer\Normalizer\ActivityPositionNormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ActivityPositionNormalizerTest extends TestCase
{
    use ProphecyTrait;

    /** @dataProvider supportDataProvider */
    public function testSupport($data, $format, $context, $expected)
    {
        $normalizer = new ActivityPositionNormalizer();
        $response = $normalizer->supportsNormalization($data, $format, $context);

        self::assertSame($expected, $response);
    }

    public function supportDataProvider()
    {
        yield 'Normalizer already call should return false' => [null, null, ['ACTIVITY_POSITION_NORMALIZER_ALREADY_CALLED' => true], false];
        yield 'Null data  should return false' => [null, null, [], false];
        yield 'Standard object data should return false' => [new \stdClass(), null, [], false];
        yield 'Empty array should return false' => [[], null, [], false];
        yield 'Array with standard class  should return false' => [[new \stdClass()], null, [], false];
        yield 'Context without group activity_position should return false' => [[], null, [AbstractNormalizer::GROUPS => ['wrong group']], false];

        $comment = new Comment();
        yield 'Array with Comment class should return true' => [[$comment], null, [AbstractNormalizer::GROUPS => ['activity_position']], true];
    }

    public function testNormalizeWithEmptyData()
    {
        $data = ['hydra:totalItems' => 0];
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::cetera())->shouldBeCalledOnce()->willReturn($data);

        $normalizer = new ActivityPositionNormalizer();
        $normalizer->setNormalizer($normalizerProphecy->reveal());

        $response = $normalizer->normalize([]);

        self::assertSame($data, $response);
    }

    public function testNormalizeWithSomeComments()
    {
        $data = [
            'hydra:totalItems' => 3,
            'hydra:member' => [
                ['id' => 1, 'createdAt' => '2025-02-20T00:01:00-05:00'],
                ['id' => 2, 'createdAt' => '2025-02-20T00:03:00-05:00'],
                ['id' => 3, 'createdAt' => '2025-02-20T00:02:00-05:00'],
                ['id' => 4, 'createdAt' => '2025-02-20T00:02:00-05:00'],
            ],
        ];
        $normalizerProphecy = $this->prophesize(NormalizerInterface::class);
        $normalizerProphecy->normalize(Argument::cetera())->shouldBeCalledOnce()->willReturn($data);

        $normalizer = new ActivityPositionNormalizer();
        $normalizer->setNormalizer($normalizerProphecy->reveal());

        $response = $normalizer->normalize([]);

        $expected = [
            'hydra:totalItems' => 3,
            'hydra:member' => [
                ['id' => 1, 'createdAt' => '2025-02-20T00:01:00-05:00', 'position' => 1],
                ['id' => 2, 'createdAt' => '2025-02-20T00:03:00-05:00', 'position' => 4],
                ['id' => 3, 'createdAt' => '2025-02-20T00:02:00-05:00', 'position' => 3],
                ['id' => 4, 'createdAt' => '2025-02-20T00:02:00-05:00', 'position' => 2],
            ],
        ];

        self::assertSame($expected, $response);
    }
}
