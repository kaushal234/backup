<?php

declare(strict_types=1);

namespace App\Controller\MinutesOfMeeting;

use App\Entity\MinutesOfMeeting\Meeting;
use Doctrine\ORM\EntityManagerInterface;

class MeetingDuplicateController
{
    private readonly EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function __invoke(Meeting $data)
    {
        $meeting = clone $data;
        $meeting->reset();
        $this->entityManager->refresh($data);

        foreach ($data->getActions() as $action) {
            if ($action->isCompleted()) {
                continue;
            }
            $newAction = clone $action;
            $newAction->reset();
            $meeting->addAction($newAction);
        }

        foreach ($data->getContacts() as $contact) {
            $newContact = clone $contact;
            $newContact->reset();
            $meeting->addContact($newContact);
        }

        return $meeting;
    }
}
