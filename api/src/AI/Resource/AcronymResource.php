<?php

declare(strict_types=1);

namespace App\AI\Resource;

use App\Entity\Acronym;
use Doctrine\ORM\EntityManagerInterface;
use Mcp\Capability\Attribute\McpResource;
use Mcp\Capability\Attribute\McpResourceTemplate;
use Symfony\Component\Serializer\SerializerInterface;

class AcronymResource
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
    ) {
    }

    #[McpResource(
        uri: 'acronym://collection',
        name: 'acronyms',
        description: 'List of internal company acronyms and their meanings.',
    )]
    public function getAllAcronyms(): array
    {
        $products = $this->em->getRepository(Acronym::class)->findBy(
            [],
            ['acronym' => 'ASC'],
            250,
        );

        return [
            'uri' => 'acronym://collection',
            'mimeType' => 'application/json',
            'text' => $this->serializer->serialize(
                $products,
                'json',
                ['groups' => ['acronym']]
            ),
        ];
    }

    #[McpResourceTemplate(
        uriTemplate: 'acronym://{acronym}',
        name: 'acronym',
        description: 'Return the meaning and description of one internal company acronym.',
    )]
    public function getAcronym(string $acronym): array
    {
        return [
            'uri' => "acronym://$acronym",
            'mimeType' => 'application/json',
            'text' => $this->serializer->serialize(
                $this->em->getRepository(Acronym::class)->findOneBy(['acronym' => $acronym]),
                'json',
                ['groups' => ['acronym']]
            ),
        ];
    }
}
