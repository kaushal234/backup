<?php

declare(strict_types=1);

namespace App\Entity\Activity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: 'App\Repository\UserConnectionRepository')]
class UserConnection extends Activity
{
}
