<?php

declare(strict_types=1);

namespace AppBundle\Controller\Common;

use ApiBundle\Model\User;
use AppBundle\DataTable\Type\FollowedTopics\FollowedTopicsDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableFactoryAwareTrait;
use Kreyu\Bundle\DataTableBundle\DataTableTurboResponseTrait;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/followed-topics')]
class FollowedTopicsController extends AbstractController
{
    use DataTableFactoryAwareTrait;
    use DataTableTurboResponseTrait;

    #[Route('/', name: 'followed_topics_home')]
    #[Template('/followed_topics/index.html.twig')]
    public function home(#[CurrentUser] User $user, Request $request): Response|array
    {
        $datatable = $this->createDataTable(FollowedTopicsDataTableType::class, 'subscriptions', ['user' => $user->getIriId()]);
        $datatable->handleRequest($request);

        return [
            'datatable' => $datatable->createView(),
        ];
    }
}
