<?php

declare(strict_types=1);

namespace AppBundle\Controller\Chat;

use ApiBundle\Model\ApiData;
use AppBundle\Configuration\ApiValueResolverAttribute;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(path: '/chat')]
class ShowController extends AbstractController
{
    public const string RESOURCE_URL = 'ai_logs';

    #[Route('/{id}', name: 'chat_show', methods: ['GET', 'POST'])]
    #[Template(template: 'chat/show.html.twig')]
    public function __invoke(#[ApiValueResolverAttribute(parameters: ['resource' => self::RESOURCE_URL, 'allowedTypes' => ['aiLogs']])] ApiData $aiLog): array
    {
        return [
            'currentLog' => $aiLog,
            'mercure_url' => $this->getParameter('mercure.url'),
        ];
    }
}
