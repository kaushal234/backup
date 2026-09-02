<?php

declare(strict_types=1);

namespace App\AI\Tool\Directory;

use App\AI\Tool\ToolInterface;
use App\Entity\Directory\People;
use App\Entity\Directory\Phone;
use App\Repository\Directory\PeopleRepository;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;

#[AsTool(name: self::NAME, description: self::DESCRIPTION)]
#[McpTool(name: self::NAME, description: self::DESCRIPTION)]
readonly class SearchPeopleTool implements ToolInterface
{
    private const string NAME = 'search_people';
    private const string DESCRIPTION = 'Search a person (employee) in the Alvest directory by firstname, lastname or full name. Use this tool whenever the user asks information about a person referenced by their name. Returns a payload with a "status" field: "found" with full details (email, jobTitle, businessUnit, position, department, supervisor, phones) when exactly one person matches; "multiple" with a list of candidates (firstname, lastname, jobTitle, businessUnit, department) when several people match — in that case ask the user which one they meant before answering; "not_found" when nobody matches.';

    private const int MAX_RESULTS = 10;

    public function __construct(private PeopleRepository $repository)
    {
    }

    /**
     * @param string $query Firstname, lastname or full name to look for
     *
     * @return array<string, mixed>
     */
    public function __invoke(string $query): array
    {
        $query = mb_trim($query);

        if ('' === $query) {
            return ['status' => 'not_found', 'query' => $query];
        }

        $results = $this->repository->searchActiveByName($query, self::MAX_RESULTS);
        $count = \count($results);

        if (0 === $count) {
            return ['status' => 'not_found', 'query' => $query];
        }

        if (1 === $count) {
            return [
                'status' => 'found',
                'person' => $this->fullDetails($results[0]),
            ];
        }

        return [
            'status' => 'multiple',
            'query' => $query,
            'count' => $count,
            'candidates' => array_map($this->shortSummary(...), $results),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function fullDetails(People $person): array
    {
        $supervisor = $person->getSupervisor();

        return [
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'email' => $person->getEmail(),
            'jobTitle' => $person->getJobTitle(),
            'businessUnit' => $person->getBusinessUnit()?->getName(),
            'position' => $person->getPosition()?->getDescription(),
            'department' => $person->getDepartment()?->getName(),
            'supervisor' => null !== $supervisor
                ? \sprintf('%s %s', $supervisor->getFirstname(), $supervisor->getLastname())
                : null,
            'phones' => $this->phones($person),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function shortSummary(People $person): array
    {
        return [
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'jobTitle' => $person->getJobTitle(),
            'businessUnit' => $person->getBusinessUnit()?->getName(),
            'department' => $person->getDepartment()?->getName(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private function phones(People $person): array
    {
        $phones = [];

        foreach ($person->getPhones() as $phone) {
            if (!$phone instanceof Phone) {
                continue;
            }

            $phones[$phone->getType()] = $phone->getNumber();
        }

        return $phones;
    }
}
