<?php

declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Enum\SagePartsToolsEnum;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/tools')]
class SagePartsToolsController extends AbstractController
{
    public function __construct(
        private readonly SagePartsToolsEnum $sagePartsTools,
    ) {
    }

    #[Route('/{category}', name: 'sage_parts_tools_category')]
    #[Template('sage_parts_tools/list.html.twig')]
    public function showCategory(string $category): array
    {
        $tools = $this->sagePartsTools->getByCategory($category);

        return compact('category', 'tools');
    }
}
