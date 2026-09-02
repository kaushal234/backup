<?php

declare(strict_types=1);

namespace App\AI\Tool\Directory;

use App\Entity\Directory\People;
use Doctrine\ORM\EntityManagerInterface;
use Mcp\Capability\Attribute\McpTool;

#[McpTool(name: 'get_random_people', description: 'Return a random person from the Alvest directory. Informations provided are: firstname, lastname, email, job Title, businessUnit, position.')]
readonly class RandomPeopleTool
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function __invoke(): array
    {
        $people = $this->em->getRepository(People::class)->findBy(
            ['disabled' => false, 'hidden' => false],
        );

        if (0 === \count($people)) {
            return [];
        }

        /** @var People $person */
        $person = $people[array_rand($people)];

        return [
            'firstname' => $person->getFirstname(),
            'lastname' => $person->getLastname(),
            'email' => $person->getEmail(),
            'jobTitle' => $person->getJobTitle(),
            'businessUnit' => $person->getBusinessUnit()?->getName(),
            'position' => $person->getPosition()?->getDescription(),
        ];
    }
}
