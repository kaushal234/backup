<?php

declare(strict_types=1);

namespace AppBundle\Controller\Mis\ThirdPartyApp;

use AppBundle\Controller\Mis\ModuleController;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/mis/modules', defaults: ['alvest_module' => 'MOD', 'breadcrumb_label' => 'menu.modules.title', 'moduleDomain' => 'mis_modules'])]
class LightController extends ModuleController
{
}
