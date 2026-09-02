<?php

declare(strict_types=1);

namespace AppBundle\Manager\Task;

use ApiBundle\Model\ApiData;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouterInterface;

readonly class TaskManager
{
    /**
     * Open trouble ticket statuses (anything not solved/closed). Must stay in sync
     * with the count query so the drill-down links match the displayed totals.
     */
    public const array OPEN_TROUBLE_TICKET_STATUSES = [
        'PENDING',
        'IN PROGRESS',
        'AWAITING USER',
        'SOLUTION PROPOSED',
        'PENDING MOO/GKU',
        'MOO/GKU SOLUTION PROPOSED',
        'MOO/GKU AWAITING USER',
    ];
    private const string LATE = 'LATE';
    private const string DUE = 'DUE';
    private const string TOTAL = 'TOTAL';
    private const string TTS = 'TTS2';
    private const string DEROGATION = 'DEROGATION';

    public function __construct(
        private RouterInterface $router,
    ) {
    }

    public function nextTask(Request $request, ApiData $task, bool $remove = false): RedirectResponse
    {
        $session = $request->getSession();
        $tasks = $session->get('tasks');
        $session->remove('tasks');

        $next = false;
        $nextTask = null;
        foreach ($tasks as $sessionTask) {
            if ($next) {
                $nextTask = $sessionTask;
                $next = false;
            }

            if (false === $next && $sessionTask['id'] === $task['id']) {
                if ($remove) {
                    unset($tasks[$sessionTask['id']]);
                }
                $next = true;
            }
        }

        if (null === $nextTask) {
            $nextTask = current($tasks);
        }

        $session->set('tasks', $tasks);
        $route = 'TroubleTicket' === $nextTask['type'] ? 'trouble_ticket_show' : 'task_show';
        $url = $this->router->generate($route, ['id' => $nextTask['id']]);

        return new RedirectResponse($url);
    }

    public function buildMyTaskReport(iterable $data, string $assigneeIri): array
    {
        $todayFilter = (new \DateTime())->format('m/d/Y');

        $report = [
            'globalTaskRows' => [],
            'otherRows' => [],
            'columnsTotals' => [self::LATE => 0, self::DUE => 0],
        ];
        foreach ($data as $datum) {
            $moduleName = ('TroubleTicket' === $datum['@type']) ? self::TTS : ('Derogation' === $datum['@type'] ? self::DEROGATION : $datum['module']['name']);
            $moduleIri = ('TroubleTicket' === $datum['@type'] || 'Derogation' === $datum['@type']) ? null : $datum['module']['@id'];

            $isLate = null !== $datum['dueDate'] && $datum['dueDate'] < (new \DateTime())->format('Y-m-d');

            $globalTask = 'Derogation' === $datum['@type'] ? false : true;

            $bucketKey = $globalTask ? 'globalTaskRows' : 'otherRows';

            if (!isset($report[$bucketKey][$moduleName])) {
                if (self::TTS === $moduleName) {
                    $urls = $this->buildTtsUrls($todayFilter, $assigneeIri);
                } elseif (self::DEROGATION === $moduleName) {
                    $urls = $this->buildDerogationUrls($todayFilter, $assigneeIri);
                } else {
                    $urls = $this->buildTaskUrls($todayFilter, $assigneeIri, $moduleIri);
                }

                $report[$bucketKey][$moduleName] = [
                    'module' => $moduleName,
                    'late' => 0,
                    'due' => 0,
                    'total' => 0,
                    'urls' => $urls,
                ];
            }

            $key = $isLate ? 'late' : 'due';
            ++$report[$bucketKey][$moduleName][$key];
            ++$report[$bucketKey][$moduleName]['total'];

            if ($globalTask) {
                ++$report['columnsTotals'][$isLate ? self::LATE : self::DUE];
            }
        }

        $report['globalTaskRows'] = array_values($report['globalTaskRows']);
        $report['otherRows'] = array_values($report['otherRows']);

        $report['footerUrls'] = $this->buildFooterUrls($todayFilter, $assigneeIri);

        return $report;
    }

    /**
     * URLs for TTS row.
     */
    private function buildTtsUrls(string $today, string $assigneeIri): array
    {
        $base['filter_trouble_ticket']['assignee']['value'] = [$assigneeIri];
        $base['filter_trouble_ticket']['status']['value'] = self::OPEN_TROUBLE_TICKET_STATUSES;

        $dueQuery = $base;
        $dueQuery['filter_trouble_ticket']['dueDate']['value']['from'] = $today;

        $lateQuery = $base;
        $lateQuery['filter_trouble_ticket']['dueDate']['value']['to'] = $today;

        return [
            self::LATE => '/en/private/mis/trouble-tickets?'.
                http_build_query($lateQuery, '', '&', \PHP_QUERY_RFC3986),
            self::DUE => '/en/private/mis/trouble-tickets?'.
                http_build_query($dueQuery, '', '&', \PHP_QUERY_RFC3986),
            self::TOTAL => '/en/private/mis/trouble-tickets?'.
                http_build_query($base, '', '&', \PHP_QUERY_RFC3986),
        ];
    }

    /**
     * URLs for Task modules.
     */
    private function buildTaskUrls(string $today, string $assigneeIri, ?string $moduleIri): array
    {
        $base['filter_task']['assignee']['value'] = [$assigneeIri];
        $base['filter_task']['module']['value'] = [$moduleIri];

        $dueQuery = $base;
        $dueQuery['filter_task']['dueDate']['value']['from'] = $today;

        $lateQuery = $base;
        $lateQuery['filter_task']['dueDate']['value']['to'] = $today;

        return [
            self::LATE => '/en/private/tasks/search?'.http_build_query($lateQuery, '', '&', \PHP_QUERY_RFC3986),
            self::DUE => '/en/private/tasks/search?'.http_build_query($dueQuery, '', '&', \PHP_QUERY_RFC3986),
            self::TOTAL => '/en/private/tasks/search?'.http_build_query($base, '', '&', \PHP_QUERY_RFC3986),
        ];
    }

    /**
     * URLs for Derogation row.
     * Derogation not an a datatable so filter differs.
     */
    private function buildDerogationUrls(string $today, string $assigneeIri): array
    {
        $base['assignee'] = $assigneeIri;

        $lateQuery = $base;
        $lateQuery['dueDateBefore'] = $today;

        $dueQuery = $base;
        $dueQuery['dueDateAfter'] = $today;

        return [
            self::LATE => '/en/private/quality/derogations?'.
                http_build_query($lateQuery, '', '&', \PHP_QUERY_RFC3986),
            self::DUE => '/en/private/quality/derogations?'.
                http_build_query($dueQuery, '', '&', \PHP_QUERY_RFC3986),
            self::TOTAL => '/en/private/quality/derogations?'.
                http_build_query($base, '', '&', \PHP_QUERY_RFC3986),
        ];
    }

    /**
     * Footer URLs (global Tasks only, not Derogations).
     */
    private function buildFooterUrls(string $today, string $assigneeIri): array
    {
        $base['filter_task']['assignee']['value'] = [$assigneeIri];

        $lateQuery = $base;
        $lateQuery['filter_task']['dueDate']['value']['to'] = $today;

        $dueQuery = $base;
        $dueQuery['filter_task']['dueDate']['value']['from'] = $today;

        return [
            self::LATE => '/en/private/tasks?'.
                http_build_query($lateQuery, '', '&', \PHP_QUERY_RFC3986),
            self::DUE => '/en/private/tasks?'.
                http_build_query($dueQuery, '', '&', \PHP_QUERY_RFC3986),
            self::TOTAL => '/en/private/tasks?'.
                http_build_query($base, '', '&', \PHP_QUERY_RFC3986),
        ];
    }
}
