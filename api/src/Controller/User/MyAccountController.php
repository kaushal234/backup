<?php

declare(strict_types=1);

namespace App\Controller\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class MyAccountController extends AbstractController
{
    public function __invoke()
    {
        return $this->getUser();
    }
}
