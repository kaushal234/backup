<?php

declare(strict_types=1);

namespace App\Notifier\Quality\NonConformity;

use App\Entity\Quality\NonConformity;
use App\Repository\Directory\PeopleRepository;

class RecipientsFinder
{
    private readonly PeopleRepository $peopleRepository;

    public function __construct(PeopleRepository $peopleRepository)
    {
        $this->peopleRepository = $peopleRepository;
    }

    public function findTos(NonConformity $nonConformity): array
    {
        return $this->peopleRepository->findGroupMembers('NOTIFY.NCR.NEW', $nonConformity->location);
    }

    public function findCcs(NonConformity $nonConformity): array
    {
        return $this->peopleRepository->findGroupsMembers(['ROLE_QAM', 'ROLE_PM', 'ROLE_QE', 'ROLE_GL', 'ROLE_PS', 'role_MPE'], $nonConformity->location);
    }
}
