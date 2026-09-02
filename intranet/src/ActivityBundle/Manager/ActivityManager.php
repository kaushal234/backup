<?php

declare(strict_types=1);

namespace ActivityBundle\Manager;

use ApiBundle\Client;
use Symfony\Component\HttpFoundation\RequestStack;

class ActivityManager
{
    /** @var RequestStack */
    protected $requestStack;

    /** @var Client */
    protected $client;

    public function __construct(RequestStack $requestStack, Client $client)
    {
        $this->requestStack = $requestStack;
        $this->client = $client;
    }

    /**
     * Display the activity (logs + comments) of a resource.
     *
     * @param string $resourceIriId
     * @param bool   $displayComments
     * @param bool   $displayLogs
     * @param string $backUrl
     */
    public function show($resourceIriId, $displayComments = true, $displayLogs = true, $backUrl = '/'): array
    {
        $this->requestStack->getSession()->set('backUrl_'.$resourceIriId, $backUrl);

        $logs = null;
        $comments = null;
        $activities = null;

        if ($displayLogs) {
            $logs = $this->client->findBy('logs', ['resource' => $resourceIriId]);
        }

        if ($displayComments) {
            $comments = $this->client->findBy('comments', ['resource' => $resourceIriId]);
        }

        if ($displayLogs && $displayComments) {
            $activities = array_merge($logs->all(), $comments->all());
        }

        if ($activities) {
            usort($activities, static function ($a, $b) {
                $a = new \DateTime($a['createdAt']);
                $b = new \DateTime($b['createdAt']);
                if ($a === $b) {
                    return 0;
                }

                return ($a > $b) ? -1 : 1;
            });
        }

        return [
            'resourceIriId' => $resourceIriId,
            'activities' => $activities,
            'comments' => $comments,
            'logs' => $logs,
        ];
    }
}
