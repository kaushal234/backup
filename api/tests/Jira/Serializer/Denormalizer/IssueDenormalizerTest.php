<?php

declare(strict_types=1);

namespace App\Tests\Jira\Serializer\Denormalizer;

use ApiPlatform\Metadata\Get;
use App\Jira\Enum\CustomField;
use App\Jira\Resources\TroubleTicketIssue;
use App\Jira\Resources\UserStoryIssue;
use App\Jira\Serializer\Denormalizer\IssueDenormalizer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class IssueDenormalizerTest extends TestCase
{
    use ProphecyTrait;

    public function testSupportsDenormalization()
    {
        $denormalizer = new IssueDenormalizer();

        self::assertTrue($denormalizer->supportsDenormalization([], TroubleTicketIssue::class, null, ['operation' => new Get()]));
        self::assertTrue($denormalizer->supportsDenormalization([], UserStoryIssue::class, null, ['operation' => new Get()]));
        self::assertFalse($denormalizer->supportsDenormalization([], TroubleTicketIssue::class, null, ['ISSUE_DENORMALIZER_ALREADY_CALLED' => true, 'operation' => new Get()]));
        self::assertFalse($denormalizer->supportsDenormalization([], UserStoryIssue::class, null, ['ISSUE_DENORMALIZER_ALREADY_CALLED' => true, 'operation' => new Get()]));
        self::assertFalse($denormalizer->supportsDenormalization([], 'Bar'));
    }

    public function testTroubleTicketIssueIsDenormalized()
    {
        $issueDenormalizer = new IssueDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);

        $expectedData = [
            'endDueDate' => '2025-12-31',
            'troubleTicketId' => 1234,
            'userStoryId' => null,
            'startDueDate' => '2025-12-23',
            'priority' => [
                'id' => '1',
                'name' => 'HIGH',
                'description' => 'High Priority',
            ],
            'status' => 'PENDING',
            'id' => '1',
        ];

        $denormalizerProphecy->denormalize(Argument::any(), 'type', 'json', ['ISSUE_DENORMALIZER_ALREADY_CALLED' => true])
            ->shouldBeCalledOnce()
            ->willReturn($expectedData);

        $issueDenormalizer->setDenormalizer($denormalizerProphecy->reveal());

        $data = [
            'fields' => [
                'priority' => [
                    'id' => '1',
                    'name' => 'HIGH',
                    'description' => 'High Priority',
                ],
                'status' => [
                    'name' => 'PENDING',
                ],
                'customfield_10048' => '1234',
                'customfield_10085' => 'null',
                'customfield_10020' => [
                    CustomField::TroubleTicketNumber->value => '1234',
                    CustomField::Sprint->value => [
                        ['startDate' => '2025-12-23', 'endDate' => '2025-12-31'],
                    ],
                ],
            ],
            'key' => '1',
        ];

        $denormalizedData = $issueDenormalizer->denormalize($data, 'type', 'json', []);
        self::assertSame($expectedData, $denormalizedData);
    }

    public function testUserStoryIssueIsDenormalized()
    {
        $issueDenormalizer = new IssueDenormalizer();
        $denormalizerProphecy = $this->prophesize(DenormalizerInterface::class);

        $expectedData = [
            'endDueDate' => '2025-12-31',
            'troubleTicketId' => null,
            'userStoryId' => 50,
            'startDueDate' => '2025-12-23',
            'priority' => [
                'id' => '2',
                'name' => 'MEDIUM',
                'description' => 'Medium Priority',
            ],
            'status' => 'IN_PROGRESS',
            'id' => '2',
        ];

        $denormalizerProphecy->denormalize(Argument::any(), 'type', 'json', ['ISSUE_DENORMALIZER_ALREADY_CALLED' => true])
            ->shouldBeCalledOnce()
            ->willReturn($expectedData);

        $issueDenormalizer->setDenormalizer($denormalizerProphecy->reveal());

        $data = [
            'fields' => [
                'priority' => [
                    'id' => '2',
                    'name' => 'MEDIUM',
                    'description' => 'Medium Priority',
                ],
                'status' => [
                    'name' => 'IN_PROGRESS',
                ],
                'customfield_10048' => 'null',
                'customfield_10085' => 50,
                'customfield_10020' => [
                    ['startDate' => '2025-12-23', 'endDate' => '2025-12-31'],
                ],
            ],
            'key' => '2',
        ];

        $denormalizedData = $issueDenormalizer->denormalize($data, 'type', 'json', []);
        self::assertSame($expectedData, $denormalizedData);
    }
}
