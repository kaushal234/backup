<?php

declare(strict_types=1);

namespace AppBundle\Trait\Mis\TroubleTicket;

use AppBundle\Controller\Directory\PeopleController;

/**
 * Shared "who will be notified" CIO/CFO/CEO names for the Trouble Ticket add/edit
 * forms (trouble_ticket.message.information). Expects the using controller to expose
 * an ApiBundle\Client as $this->client.
 */
trait CiosNamesTrait
{
    private function getCiosNames(): string
    {
        $cios = $this->client->findBy(PeopleController::RESOURCE_URL, ['acls.group.name' => ['ROLE_GCEO', 'ROLE_GCFO', 'ROLE_CIO']]);

        $names = array_reduce($cios->getSimpleArrayCopy(), static function ($memo, $cio) {
            $memo[] = \sprintf('%s %s', $cio['firstname'], $cio['lastname']);

            return $memo;
        }, []);

        return implode(', ', $names);
    }
}
