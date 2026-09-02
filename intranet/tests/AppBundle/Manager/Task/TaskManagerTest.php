<?php

declare(strict_types=1);

namespace AppBundle\Manager\Task;

use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Routing\RouterInterface;

class TaskManagerTest extends TestCase
{
    use ProphecyTrait;

    /**
     * The TTS2 drill-down links must carry the same open-status filter as the count
     * query, otherwise the list shows solved/closed tickets that are not counted.
     */
    public function testTtsRowUrlsCarryOpenStatusFilter(): void
    {
        $assigneeIri = '/people/2465';
        $data = [
            [
                '@type' => 'TroubleTicket',
                'dueDate' => '2000-01-01',
            ],
        ];

        $report = $this->buildManager()->buildMyTaskReport($data, $assigneeIri);

        self::assertCount(1, $report['globalTaskRows']);
        $ttsRow = $report['globalTaskRows'][0];
        self::assertSame('TTS2', $ttsRow['module']);

        foreach (['LATE', 'DUE', 'TOTAL'] as $key) {
            $url = urldecode($ttsRow['urls'][$key]);
            self::assertStringContainsString('filter_trouble_ticket[status][value]', $url);
            foreach (TaskManager::OPEN_TROUBLE_TICKET_STATUSES as $status) {
                self::assertStringContainsString($status, $url);
            }
            self::assertStringNotContainsString('SOLVED', $url);
        }
    }

    public function testLateTroubleTicketIsCountedAsLate(): void
    {
        $report = $this->buildManager()->buildMyTaskReport(
            [['@type' => 'TroubleTicket', 'dueDate' => '2000-01-01']],
            '/people/2465'
        );

        self::assertSame(1, $report['columnsTotals']['LATE']);
        self::assertSame(0, $report['columnsTotals']['DUE']);
        self::assertSame(1, $report['globalTaskRows'][0]['late']);
    }

    private function buildManager(): TaskManager
    {
        return new TaskManager($this->prophesize(RouterInterface::class)->reveal());
    }
}
