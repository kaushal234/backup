<?php

declare(strict_types=1);

namespace AppBundle\Controller\Directory;

use ApiBundle\Client;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class RandomPeopleController
{
    public function __construct(
        private readonly Client $client,
    ) {
    }

    #[Template('directory/people/random_people.html.twig')]
    public function __invoke()
    {
        try {
            $people = $this->client->get('people/random', ['query' => ['disabled' => false, 'hidden' => false]]);
        } catch (ClientException $exception) {
            $people = null;
        }

        return [
            'people' => $people,
        ];
    }
}
