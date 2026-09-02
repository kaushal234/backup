<?php

declare(strict_types=1);

namespace App\Jira\Registry;

use App\Jira\Http\JiraClientInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class JiraClientRegistry
{
    /**
     * @param iterable<string, JiraClientInterface> $clients
     */
    public function __construct(
        #[AutowireIterator(tag: 'jira.client', indexAttribute: 'key')]
        private iterable $clients
    ) {
    }

    public function get(string $context): JiraClientInterface
    {
        foreach ($this->clients as $key => $client) {
            if ($context === $key) {
                return $client;
            }
        }

        throw new \LogicException("Unknown Jira client: $context");
    }
}
