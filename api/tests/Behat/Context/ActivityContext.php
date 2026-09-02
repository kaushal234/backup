<?php

declare(strict_types=1);

namespace App\Tests\Behat\Context;

use App\DataCollector\ActivityCollector;
use Behat\Behat\Context\Context;
use Behat\Mink\Exception\ExpectationException;

class ActivityContext implements Context
{
    use MinkAwareTrait;

    /**
     * @Then a(n) :operation log should have been inserted on resource :resource with a changeset on the property :property with values :before, :after
     * @Then a(n) :operation log should have been inserted on resource :resource with a changeset on the property :property
     */
    public function aLogShouldHaveBeenInserted(string $operation, string $resource, ?string $property = null, $before = null, $after = null)
    {
        $profile = $this->getClientSymfonyProfile();

        /** @var ActivityCollector $collector */
        $collector = $profile->getCollector('app.activity_collector');

        $logs = [];

        foreach ($collector->getLogs() as $log) {
            $logs[] = \sprintf('"%s: %s / changeset : %s"', $log->getAction(), $log->getResource(), json_encode($log->getChangeSet()));

            if ($log->getResource() === $resource && $operation === $log->getAction()) {
                if (!isset($log->getChangeSet()[$property])) {
                    continue;
                }

                if (null !== $before && null !== $after) {
                    $before = $this->parseValue($before);
                    $after = $this->parseValue($after);

                    if ($log->getChangeSet()[$property] === [$before, $after]) {
                        return true;
                    }

                    continue;
                }

                return true;
            }
        }

        throw new ExpectationException(\sprintf("can't find any %s log on resource %s (found %s)", $operation, $resource, implode(', ', $logs)), $this->getDriver());
    }

    /**
     * @Then a(n) :operation log should have been inserted on resource :resource by the authorized application :application
     */
    public function aLogShouldHaveBeenInsertedFromAuthorizedApplication(string $operation, string $resource, string $application)
    {
        $profile = $this->getClientSymfonyProfile();

        /** @var ActivityCollector $collector */
        $collector = $profile->getCollector('app.activity_collector');

        foreach ($collector->getLogs() as $log) {
            if ($log->getResource() === $resource && $operation === $log->getAction() && null !== $log->getAuthorizedApplication() && $log->getAuthorizedApplication()->name === $application) {
                return true;
            }
        }

        throw new ExpectationException(\sprintf("can't find any %s log on resource %s from authorized application %s", $operation, $resource, $application), $this->getDriver());
    }

    /**
     * @Then a comment should have been inserted on resource :resource with message :message without a username
     * @Then a comment should have been inserted on resource :resource with message :message by :username
     */
    public function aCommentShouldHaveBeenInserted(string $resource, string $message, ?string $username = null)
    {
        $profile = $this->getClientSymfonyProfile();

        /** @var ActivityCollector $collector */
        $collector = $profile->getCollector('app.activity_collector');

        $comments = [];

        foreach ($collector->getComments() as $comment) {
            $comments[] = \sprintf('"%s: %s (by %s)"', $comment['resource'], $comment['message'], $comment['user']['username'] ?? null);
            if ($comment['resource'] === $resource && $comment['message'] === $message && $username === ($comment['user']['username'] ?? null)) {
                return true;
            }
        }

        throw new ExpectationException(\sprintf("can't find a comment on resource %s (found %s)", $resource, implode(', ', $comments)), $this->getDriver());
    }

    /**
     * @Then no update log should have been inserted on resource :resource with a changeset on the property :property
     */
    public function aLogShouldNotHaveBeenInsertedWithAChangesetOnProperty(string $resource, ?string $property = null)
    {
        $profile = $this->getClientSymfonyProfile();

        /** @var ActivityCollector $collector */
        $collector = $profile->getCollector('app.activity_collector');

        foreach ($collector->getLogs() as $log) {
            if ($log->getResource() === $resource && 'update' === $log->getAction() && isset($log->getChangeSet()[$property])) {
                throw new ExpectationException(\sprintf('found an update log on resource %s on property %s', $resource, $property), $this->getDriver());
            }
        }
    }

    private function parseValue($value)
    {
        switch ($value) {
            case 'null':
                return null;
            case 'false':
                return false;
            case 'true':
                return true;
        }

        return $value;
    }
}
